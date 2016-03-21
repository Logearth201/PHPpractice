<?php
	require_once("module/common.php");
	require_once("../safe/securimage.php");
	
	$dbh = getPDO();
	$error_message = "";
	if($_SERVER["REQUEST_METHOD"] === "POST"){
		try{
			$ee = "";
			if(!isset($_POST["captcha_code"]) || !isset($_POST["authenticate_id"]) || !isset($_POST["id"]) || !isset($_POST["new_password"]) || !isset($_POST["new_password_confirmation"])){
				throw new InputMissException("リクエストの形式が正しくありません。");
			}
			//画像認証
			$image = new Securimage();
			if($image->check($_POST["captcha_code"]) !== true) {
				throw new InputMissException("画像認証の文字列が正しくありません<br>");
			}
			
			//authenticate_idの監査
			if(isset($_POST["authenticate_id"]) && mb_strlen($_POST["authenticate_id"],"UTF-8") === 100 && preg_match("/^[0-9]+$/",$_POST["authenticate_id"])){
				$authenticate_id = $_POST["authenticate_id"];
			}else{
				throw new InputMissException("エラーが発生しました。やり直してください。");
			}
			
			if(isset($_POST["id"]) && mb_strlen($_POST["id"],"UTF-8") < 10 && preg_match("/^[1-9]+[0-9]*$/",$_POST["id"])){
				$id = $_POST["id"];
			}else{
				throw new InputMissException("エラーが発生しました。やり直してください。");
			}
			
			//passwordの長さ,一致性,情報の調査
			if($_POST["new_password"] !== $_POST["new_password_confirmation"]){
				throw new InputMissException("パスワードとパスワードの確認が一致しません");
			}else if($_POST["new_password"] === ""){
				throw new InputMissException("パスワードを入力してください");
			}else if(mb_strlen($_POST["new_password"],"UTF-8") >= 100){
				throw new InputMissException("パスワードが長すぎます");
			}else if(!password_text_isvalid($_POST["new_password"])){
				throw new InputMissException("パスワードが不適切です");
			}
			
			//SQL文の転送
			$stmt = $dbh->prepare("SELECT password_forget_key,password_forget_time FROM userdata WHERE id= ? AND password_forget_key = ? AND password_forget_time > ?;");
			$stmt->execute(array($id,$authenticate_id,time()-60*60));
			
			//パスワードの変更
			if($db = $stmt->fetch(PDO::FETCH_ASSOC)){
				//パスワードの登録処理
				$password = password_hash($_POST["new_password"],PASSWORD_DEFAULT);
				$stmt = $dbh->prepare("UPDATE userdata SET password_forget_key = ? ,password_forget_time = ?, password = ? WHERE id = ?;");
				$stmt->execute(array("",0,$password,$id));
			}else{
				throw new InputMissException("エラーが発生しました。手続きの制限時間切れの可能性があるので最初から手続きをやり直してください。");
			}
		}catch(InputMissException $e){
			$e->stackTracePage();
		}catch(Exception $e){
			page_fatal_error("review_create.php-81/".$e->getMessage());
		}
	}else{
		try{
			if(isset($_GET["authenticate_id"]) && mb_strlen($_GET["authenticate_id"],"UTF-8") === 100 && preg_match("/^[0-9]+$/",$_GET["authenticate_id"])){
				$id = $_GET["id"];
			}else{
				page_error(404);
			}
			
			if(isset($_GET["id"]) && mb_strlen($_GET["id"],"UTF-8") < 10 && preg_match("/^[1-9]+[0-9]*$/",$_GET["id"])){
				$id = $_GET["id"];
			}else{
				page_error(404);
			}
			$stmt = $dbh->prepare("SELECT password_forget_key,password_forget_time FROM userdata WHERE id= ?;");
			$stmt->execute(array($id));
			$db = $stmt->fetch(PDO::FETCH_ASSOC);
			if($db){
				$password_forget_key = h($_GET["authenticate_id"]);
			}else{
				page_error(404);
			}
		}catch(InputMissException $e){
			$e->stackTracePage();
		}catch(Exception $e){
			page_fatal_error("password_reset.php-85/".$e->getMessage());
		}
	}
?>

<?php
	require_once("template/header_tkool.php");
?>

<div id="content">
	<form method="post">
		<h1>パスワードの再設定</h1>
		<span id="error_message" style="color:#F00;">
			<?php echo $error_message; ?>
		</span>
		パスワードを再設定します。再度使用するパスワードを入力してください。<br>
		<input type="hidden" name="authenticate_id" value="<?php echo h($password_forget_key); ?>"></input>
		<input type="hidden" name="id" value="<?php echo h($id); ?>"></input>
		新しいパスワード：<br>
		<input type="password" name="new_password" required></input><br>
		パスワードの確認：<br>
		<input type="password" name="new_password_confirmation" required></input><br>
		画像認証：<br>
		<?php 
			echo Securimage::getCaptchaHtml(); 
		?>
		<input type="submit" value="送信"></input>
	</form>
</div>

<?php
	require_once("template/footer_tkool.php");
?>
