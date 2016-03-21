<?php
	require_once($_SERVER['DOCUMENT_ROOT']."/module/common.php");
	require_once($_SERVER['DOCUMENT_ROOT']."/template/header_tkool.php");
	header("HTTP/1.0 403 Forbidden");
?>
<?php
	$title = "403　アクセス権限なし";
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
	<h1>アクセス権限がありません</h1>
	恐れ入りますが、このページはアクセスすることができません。10秒後トップページに戻ります。
	<h2>このエラーが表示される理由</h2>
	・URLを直接入力してアクセスした<br>
	・外部ツールやクローラなどを利用したアクセス<br>
	・外部サイトからのアクセス<br>
	・アクセス不可能領域へのアクセス<br>
</div>


<?php
	require_once($_SERVER['DOCUMENT_ROOT']."/template/footer_tkool.php");
?>