<?php
	require_once($_SERVER['DOCUMENT_ROOT']."/module/common.php");
	require_once($_SERVER['DOCUMENT_ROOT']."/template/header_tkool.php");
	header("HTTP/1.0 404 Not Found");
?>
<?php
	$title = "404　ページが見つかりません";
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
<script>
	onload = function(){
		setTimeout(function(){location.href="/index.php";},10000);
	}
</script>
<div id="content">
	<h1>ページが見つかりません</h1>
	ページが存在しません。10秒後にトップページに戻ります。戻らない場合は<a href="/">こちら</a>をクリックしてください。
</div>


<?php
	require_once($_SERVER['DOCUMENT_ROOT']."/template/footer_tkool.php");
?>