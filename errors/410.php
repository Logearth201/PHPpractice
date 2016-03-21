<?php
	require_once($_SERVER['DOCUMENT_ROOT']."/module/common.php");
	require_once($_SERVER['DOCUMENT_ROOT']."/template/header_tkool.php");
	header("HTTP/1.0 410 Gone");
?>
<?php
	$title = "410　消去済みのページです"
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
	<h1>消去済みのページ</h1>
	当ページの公開期限が切れています。10秒後トップページに戻ります。
</div>


<?php
	require_once($_SERVER['DOCUMENT_ROOT']."/template/footer_tkool.php");
?>