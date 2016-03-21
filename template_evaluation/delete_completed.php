<?php
	require_once($_SERVER['DOCUMENT_ROOT']."/module/common.php");
	require_once($_SERVER['DOCUMENT_ROOT']."/template/header_tkool.php");
	
	$suffix = "";
	if(isset($_GET["id"]) && isset($_GET["style"]) && $_GET["style"] === "comment"){
		$suffix = "?id=".(int)$_GET["id"];
	}
?>

<div id="content">
	<h1>削除完了</h1>
	正常に削除されました。<br>
	<a href="show.php<?php echo h($suffix); ?>">戻る</a>
	&nbsp;<a href="index.php">トップページに戻る</a>
</div>

<?php
	require_once($_SERVER['DOCUMENT_ROOT']."/template/footer_tkool.php");
?>