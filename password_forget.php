<?php
	require_once("module/common.php");
	
	$error_message = "ログイン時に使用しているメールアドレスを入力してください。<br>";
	if($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["mail"])){
		try{
			if($_POST["mail"] === ""){
				throw new InputMissException("メールアドレスが入力されていません。");
			}
			if(mb_strlen($_POST["mail"],"UTF-8") >= 50){
				throw new InputMissException("メールアドレスが長すぎます。");
			}
			if(!is_mail_style($_POST["mail"])){
				throw new InputMissException("メールアドレスが違います。");
			}
			//クエリの発行
			$dbh = getPDO();
			$address = $_POST["mail"];
			
			$stmt = $dbh->prepare("SELECT id,nickname FROM userdata WHERE mail= ? ORDER BY id DESC;");
			$stmt->execute(array($address));
			$result = $stmt->fetch(PDO::FETCH_ASSOC);
			if($result){
				$key = generate_randnum();
				$time = time();
				$stmt = $dbh->prepare("UPDATE userdata SET password_forget_key = ?, password_forget_time = ? WHERE id = ?;");
				$stmt->execute(array($key,$time,$result["id"]));
				
				//メール送信
				$str = $result["nickname"]."さま\r\n\r\nパスワードの再登録の申請を行います。";
				$str .= "30分以内に下記のアドレスより申請を行ってください。\r\n\r\nhttp://logwebmake.com/service/webmake/password_reset.php?id=".$result["id"]."&key=".$key;
				$str .= "\r\n注意：もしこのメールに覚えがない場合は、お手数ですがURLにアクセスせずに削除してください。また、本メールは返信できませんので注意して下さい。";
				mailsend($_POST["mail"],"[webmaketools]パスワードの更新のお知らせ",$str);
				$error_message = "";
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
	<?php 
		if($error_message !== "" || $_SERVER["REQUEST_METHOD"] === "GET"){
			?>
			<h1>パスワードを忘れたら</h1>
			<?php echo $error_message ?>
			<form method="post">
				<input type="text" name="mail"></input>
				<input type="submit" value="送信"></input>
			</form>
			<?php
		}else{
			?>
			<h1>データの送信</h1>
			該当するメールアドレスをもつユーザーが存在する場合、メールが自動的に転送されます。
			メールが転送されない場合、メールアドレスが間違っている可能性があるのでやり直してください。手続きは30分以内に行ってください。
			それ以降に行う場合は再度最初から行ってください。
			<?php
		}
	?>
</div>

<?php
	require_once("template/footer_tkool.php");
?>