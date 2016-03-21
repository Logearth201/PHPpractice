<?php
	//parameter:type,id
	require_once($_SERVER['DOCUMENT_ROOT']."/module/common.php");
	//ログインチェック
	if(!login_check()){
		header("location:login.php");
		exit;
	}
	$user_id = getLoginID();
	$dbh = getPDO();
	$error_message = "";
	
	try{
		if(!isset($_GET["id"]) || !isset($_GET["type"])){
			throw new InputMissException("該当するブックマークが見つかりません。");
		}
		$id = (int)$_GET["id"];
		$type = $_GET["type"];
		
		if($_SERVER["REQUEST_METHOD"] === "POST"){
			check_csrf();
			$stmt = $dbh->prepare("DELETE FROM bookmark WHERE user_id = ? AND booked_id = ? AND type = ?;");
			$stmt->execute(array($user_id,$id,$type));
			
			header("location:bookmark.php");
		}else{
			$stmt = $dbh->prepare("SELECT COUNT(id) FROM bookmark WHERE user_id = ? AND booked_id = ? AND type = ?;");
			$stmt->execute(array($user_id,$id,$type));
			$db = $stmt->fetch(PDO::FETCH_ASSOC);
			if(!$db || $db["COUNT(id)"] === 0){
				throw new InputMissException("要求されたブックマークは存在しません。");
			}
		}
	}catch(InputMissException $e){
		$e->stackTracePage();
	}catch(Exception $e){
		page_fatal_error("review_create.php-81/".$e->getMessage());
	}
?>

<?php
	require_once($_SERVER['DOCUMENT_ROOT']."/template/header_tkool.php");
?>
<div id="content">
	<?php
		if($error_message !== ""){
			?>
			<h2>エラー</h2>
			エラーが発生しました。やり直してください。<br>
			<a onClick="javascirpt:history.back();">戻る</a>
			<?php
		}else{
			?>
			<h2>ブックマークの削除</h2>
			選択されたブックマークを削除しますか？
			<form method="post">
				<input type="hidden" name="authenticate_id" value="<?php echo get_authenticate_id(); ?>"></input>
				<input type="submit" value="削除する"></input>
				<input type="button" value="やめる" onClick="javascirpt:history.back();"></input>
			</form>
			<?php
		}
	?>
	
	
</div>

<?php
	require_once($_SERVER['DOCUMENT_ROOT']."/template/footer_tkool.php");
?>