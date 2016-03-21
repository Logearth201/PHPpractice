<?php
	require_once($_SERVER['DOCUMENT_ROOT']."/module/common.php");
	
	if(!isset($_GET["id"]) || !preg_match("/^[0-9]*$/",$_GET["id"])){
		page_error(404);
	}
	$id = (int)$_GET["id"];
?>
<?php
	require_once($_SERVER['DOCUMENT_ROOT']."/template/header_tkool.php");
?>
<div id="content">
	<h1>登録完了</h1>
	<?php echo $template->title; ?>レビューがブックマークに登録されました。<br>
	<a href="show.php?id=<?php echo h($id); ?>" >記事に戻る</a>
</div>

<?php
	require_once($_SERVER['DOCUMENT_ROOT']."/template/footer_tkool.php");
?>