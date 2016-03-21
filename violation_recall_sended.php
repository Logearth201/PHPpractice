<?php
	require_once("module/common.php");
	$link_address = "";
	try{
		if(!isset($_GET["id"]) || !isset($_GET["type"]) || !isset($_GET["level"]) || !style_check($_GET["type"])){
			page_error(404);
		}
		$level = $_GET["level"];
		if($level === "review"){
			$id = (int)$_GET["id"];
			$type = $_GET["type"];
			$link_address = $type."/show.php?id=".$id;
		}else if($level === "comment"){
			$dbh = getPDO();
			$type = $_GET["type"];
			$stmt = $dbh->prepare("SELECT article_id FROM ".$type."_comment WHERE id = ?;");
			$stmt->execute(array((int)$_GET["id"]));
			if($db = $stmt->fetch(PDO::FETCH_ASSOC)){
				$id = $db["article_id"];
				$link_address = $type."/show.php?id=".$id;
			}else{
				page_error(404);
			}
		}else if($level === "user"){
			$link_address = "user.php?id=".$id;
		}else{
			throw new InputMissException("エラー:通常と異なるlevelのパラメータ設定の検知");
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

<style>
h1{
	font-size:18px;
	padding:5px;
	background-color:#EEF;
}
h2{
	font-size:18px;
	margin:0;
	margin-top:10px;
	padding:3px;
	padding-left:5px;
	background-color:#DEF;
}
</style>
<div id="content">
	<h1>送信完了</h1>
	違反報告ありがとうございます。送信された情報を基に管理者がチェックし、違反性が確認された場合は該当するユーザーや記事、コメントを削除します。
	<a href="<?php echo h($link_address) ?>">[戻る]</a>
</div>


<?php
	require_once("template/footer_tkool.php");
?>

