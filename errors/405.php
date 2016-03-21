<?php
	require_once($_SERVER['DOCUMENT_ROOT']."/module/common.php");
	require_once($_SERVER['DOCUMENT_ROOT']."/template/header_tkool.php");
	header("HTTP/1.0 405 Method not allowed");
?>
<?php
	$title = "405　正しくないメソッドによる送信";
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
	<h1>不正なメソッドによる送信</h1>
	正しくないメソッドで送信しました。GETまたはPOSTメソッドで送信が許可されていないサイトです。
	GETならPOSTメソッドで、POSTメソッドならGETメソッドでリクエストを転送しなおしてください。<br>
	10秒後にトップページに戻ります。
</div>


<?php
	require_once($_SERVER['DOCUMENT_ROOT']."/template/footer_tkool.php");
?>