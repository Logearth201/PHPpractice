<?php
	require_once($_SERVER['DOCUMENT_ROOT']."/module/common.php");
	require_once($_SERVER['DOCUMENT_ROOT']."/template/header_tkool.php");
	header("HTTP/1.0 413 Request Entity Too Large");
?>
<?php
	$title = "413　過大なファイルサイズ";
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
	<h1>過大なファイルサイズ</h1>
	ファイルサイズが大きすぎます。ブラウザバックしてやり直してください。
</div>


<?php
	require_once($_SERVER['DOCUMENT_ROOT']."/template/footer_tkool.php");
?>