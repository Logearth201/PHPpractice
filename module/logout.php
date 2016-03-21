<?php
	require_once($_SERVER['DOCUMENT_ROOT']."/module/common.php");
	if(!login_check()){
		//何もせずに戻る
		header("location:index.php");
	}else{
		//あとで使うので、値を空にするだけ
		$_SESSION["is_login"] = "0";
		$_SESSION["user_id"] = 0;
		$_SESSION["nickname"] = "園田海未(見つけたら管理人にメールして)";
		header("location:index.php");
	}
?>