<?php
	require_once("module/common.php");
	
	//値チェック
	$error_message = "";
	$dbh = getPDO();
	try{
		if($_SERVER["REQUEST_METHOD"] === "POST"){
			check_csrf();
			if(!isset($_POST["text"]) || mb_strlen($_POST["text"],"UTF-8") < 1 || mb_strlen($_POST["text"],"UTF-8") > 400){
				throw new InputMissException("入力する文字数は1～400の範囲です。");
			}
			$text = $_POST["text"];
			
			//GET_CHECK
			if(!isset($_GET["level"]) || ($_GET["level"] !== "review" && $_GET["level"] !== "comment" && $_GET["level"] !== "user")){
				throw new InputMissException("levelパラメータが異なっています。");
			}
			if(!isset($_GET["id"]) || !preg_match("/^[1-9]+[0-9]*$/",$_GET["id"])){
				page_error(404);
			}
			$level = $_GET["level"];
			$id = (int)$_GET["id"];
			$type = "user";
			if($level === "user"){
				//ユーザーの存在性
				$stmt = $dbh->prepare("SELECT COUNT(id) FROM userdata WHERE id = ?;");
				$stmt->execute(array($id));
				$db = $stmt->fetch(PDO::FETCH_ASSOC);
				if(!$db || (int)$db["COUNT(id)"] !== 1){
					throw new InputMissException("該当するデータが存在しません");
				}
			}else{
				//タイプチェック
				if(!isset($_GET["type"]) || !style_check($_GET["type"])){
					throw new InputMissException("typeパラメータが異なっています。");
				}
				$type = $_GET["type"];
				//記事の存在性
				$stmt = $dbh->prepare("SELECT COUNT(id) FROM ".$type."_".$level." WHERE id = ?;");
				$stmt->execute(array($id));
				$db = $stmt->fetch(PDO::FETCH_ASSOC);
				if(!$db || (int)$db["COUNT(id)"] !== 1){
					throw new InputMissException("該当するデータが存在しません");
				}
			}
			
			//データベースの操作
			$dbh = getPDO();
			$stmt = $dbh->prepare("INSERT INTO violation_table (type,violation_id,text) VALUES (?,?,?)");
			$stmt->execute(array($type,$id,$text));
			if($level === "user"){
				header("location:user.php?id=".$id);
			}else{
				header("location:violation_recall_sended.php?id=".$id."&type=".$type."&level=".$level);
			}
			
		}else{
			//GETチェック
			if(!isset($_GET["level"]) || ($_GET["level"] !== "review" && $_GET["level"] !== "comment" && $_GET["level"] !== "user")){
				throw new InputMissException("levelパラメータが異なっています。");
			}
			if(!isset($_GET["id"]) || !preg_match("/^[1-9]+[0-9]*$/",$_GET["id"])){
				page_error(404);
			}
			$level = $_GET["level"];
			$id = (int)$_GET["id"];
			if($level === "user"){
				//ユーザーの存在性
				$stmt = $dbh->prepare("SELECT COUNT(id) FROM userdata WHERE id = ?;");
				$stmt->execute(array($id));
				$db = $stmt->fetch(PDO::FETCH_ASSOC);
				if(!$db || (int)$db["COUNT(id)"] !== 1){
					throw new InputMissException("該当するデータが存在しません");
				}
			}else{
				//タイプチェック
				if(!isset($_GET["type"]) || !style_check($_GET["type"])){
					throw new InputMissException("typeパラメータが異なっています。");
				}
				$type = $_GET["type"];
				//記事の存在性
				$stmt = $dbh->prepare("SELECT COUNT(id) FROM ".$type."_".$level." WHERE id = ?;");
				$stmt->execute(array($id));
				$db = $stmt->fetch(PDO::FETCH_ASSOC);
				if(!$db || (int)$db["COUNT(id)"] !== 1){
					throw new InputMissException("該当するデータが存在しません");
				}
			}
		}
	}catch(InputMissException $e){
		$e->stackTracePage();
	}catch(Exception $e){
		page_fatal_error("review_create.php-81/".$e->getMessage());
	}
?>

<?php
	require_once("template/header_tkool.php");
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
	<h1>違反報告</h1>
	利用規約に違反するコンテンツが発見された場合、ここから違反報告をすることができます。
	違反コンテンツはユーザーの利便性を大幅に落とすことになります。
	また、違反内容によっては当サービスの運営が不可能になる場合もございます。
	
	<h2>違反項目の選択</h2>
	<form method="post">
		違反している規約項目<br>
		<select name="text_sub">
			<option>性的コンテンツ(フェティシズム関連を含む)
			<option>犯罪行為を推奨する行為
			<option>飲酒、タバコなど
			<option>特定人に対する誹謗中傷
			<option>無意味なコンテンツ※駄作とは違います
			<option>不正アクセス行為を助長するコンテンツ
			<option>その他不適切なコンテンツ
			<option>容量圧迫行為、スパム
			<option>不正サイトへのリンク、不当な宣伝
		</select><br>
		備考(1文字～400文字)：<br>
		<textarea name="text" required minlength=1 maxlength=400></textarea>
		<input type="hidden" name="authenticate_id" value="<?php echo get_authenticate_id(); ?>"></input>
		<input type="submit" value="送信"></input>
	</form>
</div>


<?php
	require_once("template/footer_tkool.php");
?>

