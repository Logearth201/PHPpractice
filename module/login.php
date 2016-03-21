<?php
	$ssl = true;
	require_once($_SERVER['DOCUMENT_ROOT']."/module/common.php");
	
	$error_text = "";
	
	function time_check($misstime,$timestamp){
		if($misstime < 3){
			return true;
		}else{
			$nowtime = time();
			$savetime = (int)$timestamp;
			if($savetime + 60 * 60 * 7 <= $nowtime){
				return true;
			}
		}
		return false;
	}
	
	//値チェック
	if($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["mail"]) && isset($_POST["password"])){
		try{
			$dbh = getPDO();
			$mail = $_POST["mail"];
			$password = $_POST["password"];
			if(strlen($mail) === 0){
				throw new Exception("メールアドレスが未入力です。");
			}else if(strlen($mail) > 500){
				throw new Exception("メールアドレスは500文字を超過できません");
			}
			
			if(strlen($password) === 0){
				throw new Exception("パスワードが未入力です。");
			}else if(strlen($password) > 5000){
				throw new Exception("パスワードはそんなに長いはずありません(by 矢澤にこ)");
			}
			
			$stmt = $dbh->prepare("SELECT id,password,nickname,login_misstime,login_miss_timestamp FROM userdata WHERE mail = ?;");
			$stmt->execute(array($mail));
			
			//現在はPASSWORD_BCRYPTと等値
			if($userdata = $stmt->fetch(PDO::FETCH_ASSOC)){
				if(password_verify($password,$userdata["password"]) && time_check($userdata["login_misstime"],$userdata["login_miss_timestamp"])){
					//idとsessionの発行
					$_SESSION["is_login"] = "1";
					$_SESSION["user_id"] = $userdata["id"];
					$_SESSION["nickname"] = $userdata["nickname"];
					
					//パスワード再設定回数の設定
					$stmt = $dbh->prepare("UPDATE userdata SET login_misstime = 0 WHERE id= ?");
					$stmt->execute(array($userdata["id"]));
					header("location:index.php");
					exit;
				}else{
					$error_text = "メールアドレスまたはパスワードが違います";
					
					//パスワード再設定回数の設定
					if($userdata["login_misstime"] < 3){
						$setvalue = $userdata["login_misstime"] + 1;
						$time = (int)time();
						$stmt = $dbh->prepare("UPDATE userdata SET login_misstime = ".$setvalue.", login_miss_timestamp = ? WHERE id = ?;");
						$stmt->execute(array($time,$userdata["id"]));
					}
				}
			}else{
				$error_text = "メールアドレスまたはパスワードが違います";
			}
		}catch(PDOException $e){
			$error_text = "内部エラーが発生しました。やり直してください。";
		}catch(Exception $e){
			$error_text = $e->getMessage();
		}
		header("HTTP/1.0 401 Unauthorized");
	}
?>

<?php
	require_once($_SERVER['DOCUMENT_ROOT']."/template/header_tkool.php");
?>
<div id="content">
	<h1>ログインフォーム</h1>
	IDとパスワードを入力してください。未登録の場合は<a href="signin.php">新規登録</a>を押して登録してください。
	なお、Cookieを無効にするとログインできません。注意して下さい。<br><br>
	<?php echo($error_text !== "" ? $error_text."<br>" : ""); ?>
	<form method="POST">
		ID：<br>
		<input type="text" name="mail" required></input><br>
		パスワード：<br>
		<input type="password" name="password" required></input><br>
		<input type="submit" value="ログイン"></input>
	</form>
	<br><a href="password_forget.php">パスワードを忘れた場合
</div>


<?php
	require_once($_SERVER['DOCUMENT_ROOT']."/template/footer_tkool.php");
?>