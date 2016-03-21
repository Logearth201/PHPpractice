<?php
	require_once($_SERVER['DOCUMENT_ROOT']."/module/common.php");
	require_once($_SERVER['DOCUMENT_ROOT']."/template/header_tkool.php");
?>

<div id="content">
	<h1>ERROR!</h1>
	<?php
		if(isset($_SESSION) && isset($_SESSION["error_message"])){
			echo h($_SESSION["error_message"]);
		}else{
			echo "エラーが発生しました。やり直してください。";
		}
	?>
	<h2>注意！</h2>
	・Cookieを無効にしている場合、正しいエラーが表示されませんので有効にしてください。
	・エラーメッセージに書かれている注意書きに従って訂正をしてください。
	・下の戻るボタンまたは、ブラウザバックをしてやり直してください。
	<input type="button" value="戻る" onClick="javascirpt:history.back();"></input>
</div>

<?php
	require_once($_SERVER['DOCUMENT_ROOT']."/template/footer_tkool.php");
?>