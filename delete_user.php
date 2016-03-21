<?php
	require_once("module/common.php");
	require_once("safe/securimage.php");
	if(!login_check()){
		header("location:login.php");
	}
	
	$id = getLoginID();
	
	$error_message = "";
	if($_SERVER["REQUEST_METHOD"] === "POST"){
		//CSRFチェック
		try{
			check_csrf();
			//画像認証の値チェック
			$image = new Securimage();
			if($image->check($_POST["captcha_code"]) !== true) {
				throw new InputMissException("画像認証の文字列が正しくありません");
			}else{
				//LOGOUT
				$_SESSION["is_login"] = "0";
				$_SESSION["user_id"] = 0;
				$_SESSION["nickname"] = "";
				
				//SQLの発行および削除処理
				$dbh = getPDO();
				$stmt = $dbh->prepare("DELETE FROM userdata WHERE id= ?;");
				$stmt->execute(array($id));
				
				header("location:delete_user_completed.php");
				exit;
			}
		}catch(InputMissException $e){
			$e->stackTracePage();
		}catch(Exception $e){
			page_fatal_error("review_create.php-81/".$e->getMessage());
		}
	}
?>
<?php
	require_once("template/header_tkool.php");
?>
<div id="content">
	<h1>削除申請</h1>
	警告：あなたはユーザーを削除しようとしています！一度削除した情報は二度と復帰することができません。
	作成したコンテンツがすべて消えてしまいます。それでも削除しますか？<br><br>
	削除する場合は、下の画像認証を入力して、「削除」ボタンを押してください。
	<form method="post">
		<input type="hidden" name="authenticate_id" value="<?php echo get_authenticate_id(); ?>"></input>
		<?php echo $error_message ?>
		画像認証：<br>
		<?php 
			echo Securimage::getCaptchaHtml(); 
		?>
		<br>
		※正しいテキストを入力してください。誤削除対策のため設けております。よろしくお願いします。<br>
		<input type="submit" value="削除"></input>
	</form>
</div>
<?php
	require_once("template/footer_tkool.php");
?>