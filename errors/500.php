<?php
	require_once($_SERVER['DOCUMENT_ROOT']."/module/common.php");
	require_once($_SERVER['DOCUMENT_ROOT']."/template/header_tkool.php");
	header("HTTP/1.0 500 Internal Server Error");
	
	//メールで報告する(私のほうへ)
	if($_SESSION["error_message"] !== ""){
		
	}
?>
<?php
	$title = "500 内部エラー";
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
	<h1>内部エラーの発生</h1>
	エラーが発生しました。次の文章をお読みください。
	<?php echo h($_SESSION["error_message"]); ?>
	<ul>
	<li>UTF-8以外の文字コードを利用していない場合は、UTF-8の文字列を利用しなければなりません。</li>
	<li>それ以外の場合:もしこの画面を見るのが初回の場合は、ブラウザバックしてもう一度やり直してください。</li>
	<li>サーバーが動作しない、サーバーがダウンしている場合もございます。その場合はしばらくお待ちください。</li>
	<li>これらの措置を取ってもなおこの画面が表示される場合、エラー報告にてエラーを報告してください。</li>
	</ul>
</div>


<?php
	require_once($_SERVER['DOCUMENT_ROOT']."/template/footer_tkool.php");
?>