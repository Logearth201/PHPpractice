<?php
	$ssl = true;
	require_once("module/common.php");
	require_once("safe/securimage.php");
	
	function mailsender($to,$subject,$body,$fromname,$fromaddress){
		//SMTP送信
		$mail = new Qdmail();
		$mail -> smtp(true);
		$param = array(
			'host'=>'（SMTPサーバー名',
			'port'=> 587 ,
			'from'=>'',
			'protocol'=>'SMTP_AUTH',
			'user'=>'（SMTP認証ユーザー名）',
			'pass' => '（SMTP認証パスワード）',
		);
		$mail ->smtpServer($param);
		$mail ->to($to);
		$mail ->subject($subject);
		$mail ->from($fromaddress,$fromname);
		$mail ->text($body);
		$return_flag = $mail ->send();
		return $return_flag;
	}
	
	$error_message = "";
	if($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["mail"]) && isset($_POST["captcha_code"])){
		$dbh = getPDO();
		try{
			//画像認証の値チェック
			$image = new Securimage();
			if($image->check($_POST["captcha_code"]) !== true) {
				throw new InputMissException("画像認証の文字列が正しくありません");
			}
			
			if($_POST["mail"] === ""){
				$error_message .= "メールアドレスを入力してください<br>";
			}else if(strlen($_POST["mail"]) >= 50){
				$error_message .= "メールアドレスが長すぎます<br>";
			}else if(!is_mail_style($_POST["mail"])){
				$error_message .= "メールアドレスが違います<br>";
			}
			$mail = $_POST["mail"];
			
			//userのmailの重複チェック
			$stmt = $dbh->prepare("SELECT COUNT(*) AS cnt FROM userdata WHERE mail= ?;");
			$stmt->execute(array($mail));
			if($data = $stmt->fetch(PDO::FETCH_ASSOC)){
				if((int)$data["cnt"] !== 0){
					$error_message .= "該当メールアドレスは既に登録済みです。";
				}
			}else{
				$error_message .= "エラーが発生しました。<br>";
			}
			
			//重複するメールが存在する場合は、そのメールを強制削除する
			$stmt = $dbh->prepare("DELETE from temp_user WHERE mail = ? OR date < ?;");
			$stmt->execute(array($mail,(int)(time()-60*60)));
			
			//ADD_KEY
			$time = time();
			$authenticate_key = generate_randnum();
			$stmt = $dbh->prepare("INSERT INTO temp_user (mail,date,authenticate_key) VALUES (?,?,?);");
			$stmt->execute(array($mail,(int)time(),$authenticate_key));
			
			$to = $mail;
			$subject = "E2BC本登録について[返信不可]";
			$body = $mail."さま、こんにちは。\r\n";
			$body .= "仮登録ありがとうございます。\r\n";
			$body .= "本登録の手続きは以下の通りです。手続きは30分以内に完了してください。\r\n";
			$body .= "１：下に記述されているアドレスにアクセスする。\r\n";
			$body .= "http://logwebmake.com/service/cloudtkool/register_information.php?information=".$time."_".$authenticate_key."\r\n";
			$body .= "２：フォームに必要な情報を入力してください。\r\n\r\n";
			$body .= "これで本登録完了です。もしこのメールについてご存じない場合、お手数ですが本メールの削除をお願いします。\r\n\r\nE2BC運営\r\nlog";
			mailsend($to,$subject,$body,"クラウドツクール運営","header: cloudtkool@jcom.home.ne.jp");
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
		if($_SERVER["REQUEST_METHOD"] === "GET" || $error_message !== ""){
			?>
			<h1>ユーザー登録</h1>
			メールアドレスを入力してください。入力されたアドレスが、ログインするときに使用するアドレスとなります。
			<span id="error_message" style="color:#F00;">
				<?php echo $error_message; ?>
			</span>
			<form method="POST">
				メールアドレス：<br>
				<input type="text" name="mail" maxlength=100 required></input><br>
				画像認証：<br>
				<?php 
					echo Securimage::getCaptchaHtml(); 
				?>
				<br>
				※スパム対策のため行っています。ご協力ください。<br>
				※登録を押すと、<a href="rule.php">利用規約</a>にすべて同意したものとみなします。<br>
				<input type="submit" value="登録"></input>
			</form>
			<?php
		}else{
			?>
			<h1>仮登録完了の通知</h1>
			メールを送信しました。メールに記述されているURLにアクセスして、登録作業を完了してください。
			メールが届かない場合は、この作業をやり直してください。メールが届いたのち30分以内にこの作業を完了させてください。
			なお、メールが迷惑メールに届いている可能性があります。メールが届かない場合は「迷惑メール」も参照ください。
			タイトルは「クラウドツクール本登録について」です。
			<?php
		}
	?>
</div>

<?php
	require_once("template/footer_tkool.php");
?>