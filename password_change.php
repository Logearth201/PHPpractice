<?php
	require_once("module/common.php");
	if(!login_check()){
		header("location:login.php");
	}
	
	$id = (int)getLoginID();
	$dbh = getPDO();
	$error_message = "";
	try{
		$stmt = $dbh->prepare("SELECT year,webpage,sex,mail,nickname,self_introduce,password FROM userdata WHERE id=?");
		$stmt->execute(array($id));
		$db = $stmt->fetch(PDO::FETCH_ASSOC);
		
		if($_SERVER["REQUEST_METHOD"] === "POST"){
			//POST送信
			$nickname = $_POST["nickname"];
			$mail = $_POST["mail"];
			$self_introduce = $_POST["self_introduce"];
			
			//メールチェック
			if($mail === ""){
				throw new InputMissException("メールアドレスを入力してください。");
			}else if(mb_strlen($mail,"UTF-8") >= 50){
				throw new InputMissException("メールアドレスが長すぎます");
			}else if(!is_mail_style($mail)){
				throw new InputMissException("メールアドレスが違います");
			}
			
			//自己紹介の長さチェック
			if(mb_strlen($self_introduce,"UTF-8") > 500){
				throw new InputMissException("自己紹介が長すぎます");
			}
			
			//性別パラメータのチェック
			$sex = (int)$_POST["sex"];
			if($sex !== 1 && $sex !== 0 && $sex !== -1){
				throw new InputMissException("性別パラメータが正しく設定されていません");
			}
			
			//年齢チェック
			$year = (int)$_POST["year"];
			if($year < -1 || $year > 9){
				throw new InputMissException("年齢パラメータが正しく設定されていません");
			}
			
			//Webページのチェック
			$webpage = check_url_info();
			
			//新しいパスワードのチェック
			if(!password_text_isvalid($_POST["new_password"])){
				throw new InputMissException("パスワードが不適切です");
			}else if(mb_strlen($_POST["new_password"],"UTF-8") >= 100){
				throw new InputMissException("パスワードが長すぎます");
			}
			
			//パスワードに記述がある場合の処理
			if(isset($_POST["password"]) && mb_strlen($_POST["password"],"UTF-8") > 0){
				if(isset($_POST["new_password"]) && mb_strlen($_POST["new_password"],"UTF-8") > 0 && isset($_POST["new_password_confirmation"]) && mb_strlen($_POST["new_password_confirmation"],"UTF-8") > 0){
					$password = $_POST["password"];
					$new_password = $_POST["new_password"];
					$new_password_confirmation = $_POST["new_password_confirmation"];
					if($new_password !== $new_password_confirmation){
						throw new InputMissException("パスワードの確認とパスワードが一致しません");
					}else if(!password_verify($password,$db["password"])){
						throw new InputMissException("パスワードが違います");
					}else{
						$stmt = $dbh->prepare("UPDATE userdata SET nickname = ?, mail = ?, self_introduce = ?, sex = ?, year = ?,password = ?, webpage = ? WHERE id = ?;");
						$stmt->execute(array($nickname,$mail,$self_introduce,$sex,$year,password_hash($new_password,PASSWORD_DEFAULT),$webpage,$id));
						$_SESSION["nickname"] = $nickname;
					}
				}else{
					throw new Exception("パスワードの変更枠に入力されていないものがあります。");
				}
			}else{
				$stmt = $dbh->prepare("UPDATE userdata SET nickname = ?, mail = ?, self_introduce = ?, sex = ?, year = ?, webpage = ? WHERE id = ?;");
				$stmt->execute(array($nickname,$mail,$self_introduce,$sex,$year,$webpage,$id));
				$_SESSION["nickname"] = $nickname;
			}
			header("location:user.php?id=".$id);
		}
	}catch(InputMissException $e){
		$e->stackTracePage();
	}catch(Exception $e){
		page_fatal_error("review_create.php-81/".$e->getMessage());
	}
?>

<?php
	require_once("template/header_tkool.php");
?>
<div id="content">
	<h1>プレイヤー情報の変更</h1>
	<?php echo $error_message; ?>
	<form method="post">
		ニックネーム：<br>
		<input type="text" name="nickname" value="<?php echo h($db["nickname"]); ?>" required></input><br>
		メールアドレス：<br>
		<input type="text" name="mail" value="<?php echo h($db["mail"]); ?>" required></input><br>
		自己紹介：<br>
		<textarea name="self_introduce"><?php echo h($db["self_introduce"]); ?></textarea><br>
		Webページ・ブログ：<br>
		<textarea name="information_fromurl" maxlength=1500><?php echo h($db["webpage"]); ?></textarea><br>
		性別<br>
		<input type="radio" name="sex" value="1" <?php if((int)$db["sex"]===1)echo "checked"; ?>>男
		<input type="radio" name="sex" value="0" <?php if((int)$db["sex"]===0)echo "checked"; ?>>女
		<input type="radio" name="sex" value="-1" <?php if((int)$db["sex"]===-1)echo "checked"; ?>>未設定
		</input><br>
		年齢：<br>
		<select name="year" value="<?php echo h($db["year"]); ?>">
			<option value="-1">未設定
			<option value="0">10歳未満
			<option value="1">10代
			<option value="2">20代
			<option value="3">30代
			<option value="4">40代
			<option value="5">50代
			<option value="6">60代
			<option value="7">70代
			<option value="8">80代
			<option value="9">90代
		</select>
		<h2>パスワードの変更</h2>
		※パスワードを変更しない場合、下欄は空白にしてください。<br>
		現在のパスワード：<br>
		<input type="password" name="password"></input><br>
		パスワードの変更：<br>
		<input type="password" name="new_password"></input><br>
		変更するパスワードの確認：<br>
		<input type="password" name="new_password_confirmation"></input><br>
		<input type="submit" value="情報の変更"></input>
	</form>
</div>
