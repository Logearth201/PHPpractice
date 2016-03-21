<?php
	require_once("sp.php");
?>
<!DOCTYPE HTML>
<html>
<head>
<meta name="viewport" content="width=520,user-scalable=no">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<?php
	//インデックスページの重複対策
	$selffile = basename($_SERVER['PHP_SELF']);
	if($selffile === "index.php"){
		echo '<link rel="canonical" href="http://www.e2bc.com/">';
	}
	//メタタグ
	if(isset($input_meta_description) && $input_meta_description !== ""){
		echo '<meta name="description" content="'.h($input_meta_description).'"/>';
	}
	if(isset($input_meta_keyword) && $input_meta_keyword !== ""){
		echo '<meta name="keyword" content="'.h($input_meta_keyword).'"/>';
	}
	$ua = new UserAgent();
	$sp = $ua->set() === "mobile";
	$develop = true;
?>
<title>
<?php
	$global_donotuse_title = "E2BC";
	if(isset($title)){
		echo $title."なら".$global_donotuse_title."！";
	}else{
		echo $global_donotuse_title."　ここでしか聞けないレビュー・評価";
	}
?>
</title>
<meta http-equiv="Content-Style-Type" content="text/css">
<link rel="stylesheet" type="text/css" href="/template/common_tkool<?php if($sp) echo "sp" ?>.css"/>

<?php
	if($sp){
?>
		<script>
			function ButtonPush(){
				document.getElementById("leftpart").hidden = !document.getElementById("leftpart").hidden;
			}
		</script>
<?php
	}
?>
<script>
	function window_close(){
		document.getElementById("login_only_func").hidden = true;
	}
	function window_show_login(){
		document.getElementById("login_only_func").hidden = false;
	}
	function window_show_error(){
		document.getElementById("error_func").hidden = false;
	}
	function window_error_func_close(){
		document.getElementById("error_func").hidden = true;
	}
</script>
</head>
<body>
<div id="container">
	<div id="headpart">
		<div id="header">
			<div id="titlelogo">
				<a href="index.php"><img src="E2BC.png" height="80px"></img></a>
			</div>
			<div id="adspace">
				
			</div>
			<?php
				if($sp){
					?>
					<div id="menu_button">
						<!--SP専用テンプレート-->
						<input type="button" id="menu_button" onClick="ButtonPush()" value="＝"></input>
						<!--SP専用　END-->
					</div>
					<?php
				}
			?>
		</div>
		<div id="topbar">
			<div id="topbar_left">
				<?php
					if(!login_check()){
						echo 'ようこそ、ゲストさん';
					}else{
						echo 'ようこそ、'.h($_SESSION["nickname"]).'さん';
					}
				?>
			</div>
			<div id="topbar_right">
				<?php
					if(!login_check()){
						echo '<a href="login.php">ログインする</a>&nbsp;&nbsp;<a href="/signin.php">新規登録</a>';
					}else{
						echo '<a href="logout.php">ログアウトする</a>';
					}
				?>
			</div>
		</div>
	</div>
	
	<div id="main">
		<div id="contentpart">
			<div id="leftpart" <?php if($sp) echo "hidden"; ?>>
				<?php
					require_once("menu.php");
				?>
				<div id="box_1">
					<div id="box_title">E2BCメニュー</div>
					<?php
						echo '<a id="even" href="/about.php">E2BCとは？</a>';
						echo '<a id="even" href="/enterprise/">企業レビュー</a>';
						echo '<a id="odd" href="/product/">商品レビュー</a>';
						if(login_check()){
							echo '<a id="even" href="user.php?id='.h(getLoginID()).'">マイページ</a>';
							echo '<a id="even" href="/bookmark.php">ブックマーク</a>';
						}else{
							echo '<a id="even" onClick="window_show_login();">ブックマーク</a>';
						}
						echo '<a id="even" href="/rule.php">免責事項</a>';
					?>
				</div>
			</div>
			<!--ここから本文-->
			<div id="centerpart">
	
