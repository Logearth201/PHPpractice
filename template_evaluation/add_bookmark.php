<?php
	require_once($_SERVER['DOCUMENT_ROOT']."/module/common.php");
	//ログインチェック
	$is_login = login_check();
	if(!login_check()){
		header("location:login.php");
		exit;
	}
	$user_id = getLoginID();
	$error_message = "";
	
	$dbh = getPDO();
	if($_SERVER["REQUEST_METHOD"] === "POST"){
		try{
			check_csrf();
			if(!isset($_GET["id"]) || !preg_match("/^[1-9][0-9]*$/",$_GET["id"])){
				throw new InputMissException("パラメータIDが設定されていません");
			}
			$id = (int)$_GET["id"];
			
			$stmt = $dbh->prepare("SELECT COUNT(id) FROM ".$template->type."_review WHERE id = ?;");
			$stmt->execute(array($id));
			$db = $stmt->fetch(PDO::FETCH_ASSOC);
			if(!$db){
				throw new InputMissException("記事の取得に失敗しました。");
			}else{
				if((int)$db["COUNT(id)"] !== 1){
					throw new InputMissException("存在しない記事をブックマークすることはできません。");
				}
			}
			
			$stmt = $dbh->prepare("SELECT COUNT(id) FROM bookmark WHERE booked_id = ? AND user_id = ? AND type = ?;");
			$stmt->execute(array($id,$user_id,$template->type));
			$db = $stmt->fetch(PDO::FETCH_ASSOC);
			if(!$db){
				throw new InputMissException("ブックマークの取得に失敗しました。");
			}else{
				if((int)$db["COUNT(id)"] !== 0){
					throw new InputMissException("選択されている記事は既にブックマーク済みです。");
				}
			}
			
			$stmt = $dbh->prepare("SELECT COUNT(id) FROM bookmark WHERE user_id = ?;");
			$stmt->execute(array($user_id));
			$db = $stmt->fetch(PDO::FETCH_ASSOC);
			if(!$db){
				throw new InputMissException("ブックマークの取得に失敗しました。");
			}else{
				if((int)$db["COUNT(id)"] > 500){
					throw new InputMissException("ブックマークできる記事数は500までです。");
				}
			}
			
			$stmt = $dbh->prepare("INSERT bookmark (user_id,type,booked_id) VALUES (?,?,?);");
			
			$stmt->execute(array((int)$user_id,$template->type,(int)$id));
			
			header("location:bookmark_registered.php?id=".(int)$id);
		}catch(InputMissException $e){
			$e->stackTracePage();
		}catch(Exception $e){
			page_fatal_error("comment_send.php//".$e->getMessage());
		}
	}else{
		try{
			if(!isset($_GET["id"]) || !preg_match("/^[1-9][0-9]*$/",$_GET["id"])){
				throw new InputMissException("パラメータIDが設定されていません");
			}
			$id = (int)$_GET["id"];
			
			$stmt = $dbh->prepare("SELECT COUNT(id) FROM ".$template->type."_review WHERE id = ?;");
			$stmt->execute(array($id));
			
			$db = $stmt->fetch(PDO::FETCH_ASSOC);
			if(!$db){
				throw new InputMissException("記事の取得に失敗しました。");
			}else{
				if((int)$db["COUNT(id)"] !== 1){
					throw new InputMissException("エラー：存在しない記事");
				}
			}
			
			$stmt = $dbh->prepare("SELECT COUNT(id) FROM bookmark WHERE booked_id = ? AND user_id = ? AND type = ?;");
			$stmt->execute(array($id,$user_id,$template->type));
			$db = $stmt->fetch(PDO::FETCH_ASSOC);
			if(!$db){
				throw new InputMissException("ブックマークの取得に失敗しました。");
			}else{
				if((int)$db["COUNT(id)"] !== 0){
					throw new InputMissException("選択されている記事は既にブックマーク済みです。");
				}
			}
		}catch(InputMissException $e){
			$e->stackTracePage();
		}catch(Exception $e){
			page_fatal_error("comment_send.php//".$e->getMessage());
		}
	}
?>
<?php
	require_once($_SERVER['DOCUMENT_ROOT']."/template/header_tkool.php");
?>
<div id="content">
	<?php echo nl2br(h($error_message)); ?>
	<?php
		if($_SERVER["REQUEST_METHOD"] === "POST" || $error_message === ""){
			?>
	選択された<?php echo $template->title; ?>レビューをブックマークに登録しますか？登録する場合は「登録する」を、しない場合は「戻る」を押すかブラウザバックしてください。
	<form method="post">
		<input type="hidden" name="authenticate_id" value="<?php echo get_authenticate_id(); ?>"></input>
		<input type="submit" value="登録する"></input>
	</form>
			<?php
		}
	?>
	<input type="button" value="戻る" onClick="javascirpt:history.back();"></input>
</div>

<?php
	require_once($_SERVER['DOCUMENT_ROOT']."/template/footer_tkool.php");
?>