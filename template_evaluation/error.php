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
	<ul>
	<li>エラーメッセージに書かれている注意書きに従って訂正をしてください。下の戻るボタンまたは、ブラウザバックをしてやり直してください。</li>
	<li>Cookieを無効にしている場合、正しい処理が行われないことがあるので注意して下さい。</li>
	<li>編集中にGoogle Chromeなどでソースを見た場合、適切に処理されない場合があります。その場合はやり直してください。</li>
	<li>外部からのリクエストが送られるなど身に覚えがない場合はブラウザを閉じることを推奨します。</li>
	</ul>
	<input type="button" value="戻る" onClick="javascirpt:history.back();"></input>
</div>

<?php
	require_once($_SERVER['DOCUMENT_ROOT']."/template/footer_tkool.php");
?>