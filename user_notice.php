<?php
	require_once("module/common.php");
	$user_id = getLoginID();
	$dbh = getPDO();
	
	$stmt_notice = $dbh->prepare("SELECT title,article_id,type FROM news WHERE user_id = ?;");
	$stmt_notice->execute(array($user_id));
	
	$stmt = $dbh->prepare("UPDATE news SET already_read = ? WHERE user_id = ?;");
	$stmt->execute(array("1",$user_id));
?>
<?php
	require_once("template/header_tkool.php");
?>
<div id="content">
	<h1>最新情報・通知</h1>
	<h2>ユーザー通知情報</h2>
	<?php
		$input_exist = false;
		while($data = $stmt_notice->fetch(PDO::FETCH_ASSOC)){
			$input_exist = true;
			$url = $data["type"]."/"."show.php?id=".$data["article_id"];
			echo '<a style="display:block;" href='.h($url).'>';
			echo h($data["title"]);
			echo '</a>';
		}
		if(!$input_exist){
			echo "最新のニュースはありません。";
		}
	?>
</div>
<?php
	require_once("template/footer_tkool.php");
?>