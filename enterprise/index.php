<?php
	require_once("template_word.php");
	require_once($_SERVER['DOCUMENT_ROOT']."/module/common.php");
	require_once($_SERVER['DOCUMENT_ROOT']."/template/header_tkool.php");
?>
<style>
	#window{
		width:100%;
		padding-top:40px;
		padding-bottom:40px;
		text-align:center;
		font-size:20px;
		background-color:#44a;
	}
	#window_second{
		width:100%;
		padding-top:40px;
		padding-bottom:40px;
		text-align:center;
		font-size:20px;
		background-color:#4a4;
	}
	#window_third{
		width:100%;
		padding-top:40px;
		padding-bottom:40px;
		text-align:center;
		font-size:20px;
		background-color:#a44;
	}
	#w_header,w_header2{
		background-color: transparent;
		background-image: none;
		font-size:32px;
		border-bottom:1px solid #66E;
		width:80%;
		margin-left:10%;
		margin-bottom:10px;
	}
	#w_header{
		border-bottom:1px solid #66E;
	}
	#w_header2{
		border-bottom:1px solid #6E6;
	}
	#w_button{
		border-bottom:1px solid rgba(255,255,255,0.5);
		text-align:center;
	}
</style>
<div id="window">
	<img style="display:hidden;width:100px;height:100px;"></img>
	<div id="w_header">企業情報ならEV-THING!</div>
	企業の口コミ情報が記載されています。
</div>
<div id="window_second">
	<img style="display:hidden;width:100px;height:100px;"></img>
	<div id="w_header">君の情報が欲しい！</div>
	ブラック企業・優良企業を投稿して情報共有！
</div>
<div id="window_wrapper">
	<div id="w_button">今すぐ見る</div>
	上のボタンをクリックすると企業情報を見ることができます。
</div>
<?php
	require_once($_SERVER['DOCUMENT_ROOT']."/template/footer_tkool.php");
?>