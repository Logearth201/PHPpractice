<?php
	require_once("module/common.php");
	require_once("safe/securimage.php");
	
	$error_message = "";
	
	try{
		if($_SERVER["REQUEST_METHOD"] === "POST"){
			//POSTのとき：画像認証の値チェック
			if(isset($_POST["mail"]) && isset($_POST["authenticate_id"]) && isset($_POST["password"]) && isset($_POST["password_confirmation"]) && isset($_POST["captcha_code"])){
				throw new InputMissException("不正なフォームの確認");
			}
			
			$image = new Securimage();
			if($image->check($_POST["captcha_code"]) !== true){
				$error_message .= "画像認証の文字列が正しくありません<br>";
			}
			$dbh = getPDO();
			
			//authenticate_idのチェック
			if(!preg_match("/^[0-9]+$/",$_POST["authenticate_id"]) || mb_strlen($_POST["authenticate_id"],"UTF-8") !== 100){
				throw new InputMissException("IDまたはメールアドレスの偽装送信が確認されました。";
				exit;
			}
			
			//mailのチェック
			if($_POST["mail"] === ""){
				throw new InputMissException("IDまたはメールアドレスの偽装送信が確認されました。";
				exit;
			}else if(mb_strlen($_POST["mail"],"UTF-8") >= 50){
				throw new InputMissException("IDまたはメールアドレスの偽装送信が確認されました。");
			}else if(!is_mail_style($_POST["mail"])){
				throw new InputMissException("IDまたはメールアドレスの偽装送信が確認されました。";
				exit;
			}
			
			$authenticate_id = (int)$_POST["authenticate_id"];
			$mail = $_POST["mail"];
			
			$stmt = $dbh->prepare("SELECT COUNT(*) AS cnt FROM temp_user WHERE authenticate_key = ? AND mail = ? AND date > ?;");
			$stmt->execute(array($authenticate_id,$mail,(int)(time()-3600)));
			
			$db = $stmt->fetch(PDO::FETCH_ASSOC);
			if(!$db || (int)$db["cnt"] !== 1){
				throw new InputMissException("IDまたはメールアドレスの偽装送信が確認されました。";
				exit;
			}
			
			//パスワードの長さチェック
			if($_POST["password"] !== $_POST["password_confirmation"]){
				$error_message .= "パスワードとパスワードの確認が一致しません<br>";
			}else if($_POST["password"] === ""){
				$error_message .= "パスワードを入力してください<br>";
			}else if(mb_strlen($_POST["password"],"UTF-8") >= 100){
				$error_message .= "パスワードが長すぎます<br>";
			}else if(!password_text_isvalid($_POST["password"])){
				$error_message .= "パスワードが不適切です<br>";
			}
			
			//ニックネームのチェック
			if(mb_strlen($_POST["nickname"],"UTF-8") >= 100){
				$error_message .= "ニックネームが長すぎます<br>";
			}else if($_POST["nickname"] === ""){
				$error_message .= "ニックネームを入力してください<br>";
			}
			$nickname = $_POST["nickname"];
			
			//mailの重複チェック
			$stmt = $dbh->prepare("SELECT COUNT(*) AS cnt FROM userdata WHERE mail= ?;");
			$stmt->execute(array($mail));
			
			if($error_message !== ""){
				throw new InputMissException($error_message);
			}
			
			$data = $stmt->fetch(PDO::FETCH_ASSOC);
			if($data){
				if((int)$data["cnt"] !== 0){
					$error_message .= "該当メールアドレスは既に登録済みです。<br>";
				}
			}else{
				throw new InputMissException("エラーが発生しました。");
			}
			
			//DB格納
			$password = password_hash($_POST["password"],PASSWORD_DEFAULT);
			$stmt = $dbh->prepare("INSERT INTO userdata (nickname,mail,password) VALUES (?,?,?);");
			$stmt->execute(array($nickname,$mail,$password));
			$stmt = $dbh->prepare("DELETE FROM temp_user WHERE mail = ?;");
			$stmt->execute(array($mail));
			
			//エラーがなければこれで終了
			header("location:useradd_complete.php");
		}else{
			//authenticate_idの情報の確認(ヘッダーはnowtime:mailの順番)
			if(!isset($_GET["information"]) || mb_strlen($_GET["information"],"UTF-8") == 0){
				throw new InputMissException("ERROR:informationパラメータの不正");
			}
			
			$pieces = explode("_",$_GET["information"]);
			if(count($pieces) !== 2){
				throw new InputMissException("ERROR:informationパラメータの不正");
			}
			
			$authenticate_id = $pieces[1];
			$id = $pieces[0];
			
			if(!preg_match("/^[1-9]+[0-9]*$/",$pieces[0]) || !preg_match("/^[0-9]+$/",$pieces[1]) || mb_strlen($pieces[0],"UTF-8") > 15 || mb_strlen($pieces[1],"UTF-8") !== 100){
				throw new InputMissException("ERROR:informationパラメータの不正");
			}
			
			$dbh = getPDO();
			$stmt = $dbh->execute("SELECT mail FROM temp_user WHERE authenticate_key = ? AND date = ? AND date > ?;");
			$stmt->execute(array($authenticate_id,$id,(int)(time()-3600)));
			$db = $stmt->fetch(PDO::FETCH_ASSOC);
			if(!$db){
				throw new InputMissException("エラーが発生しました。登録作業をやり直してください。仮登録の作業から30分以上経過している場合は、本登録ができません。次は30分経過するまでに本登録をお願いします。");
			}
			$mail = $db["mail"];
		}
	}catch(InputMissException $e){
		//なにもしない。入力ミスはきちんとエラー表示させる。
	}catch(Exception $e){
		page_fatal_error("register_information.php-128/".$e->getMessage());
	}
?>

<?php
	require_once("template/header_tkool.php");
?>

<div id="content">
	<h1>ユーザー登録</h1>
	<form method="POST">
		登録情報を入力してください。
		<span id="error_message" style="color:#F00;">
			<?php echo $error_message; ?>
		</span>
		<input type="hidden" name="authenticate_id" value="<?php echo h($authenticate_id); ?>"></input>
		<input type="hidden" name="mail" value="<?php echo h($mail) ?>"></input>
		<br>ニックネーム(HN)：<br>
		<input type="text" name="nickname" required></input><br>
		パスワード(8文字以上推奨)：<br>
		<input type="password" name="password" required></input><br>
		パスワードの確認：<br>
		<input type="password" name="password_confirmation" required></input><br>
		画像認証：<br>
		<?php 
			echo Securimage::getCaptchaHtml(); 
		?>
		<br>
		※スパム対策のため行っています。ご協力ください。<br>
		※登録を押すと、<a href="rule.php">利用規約</a>にすべて同意したものとみなします。<br>
		<input type="submit" value="登録"></input>
	</form>
</div>

<?php
	require_once("template/footer_tkool.php");
?>