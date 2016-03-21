<?php
	require_once($_SERVER['DOCUMENT_ROOT']."/module/common.php");
	$title = $template->title."レビューのコメント修正";
	$error_message = "";
	
	if(!login_check()){
		header("location:login.php");
		exit;
	}
	
	//値チェック(GET)
	if(!isset($_GET["id"]) || preg_match("/$[1-9]+[0-9]*^/",$_GET["id"])){
		page_error(404);
	}
	$id = (int)$_GET["id"];
	$user_id = getLoginID();
	
	
	if($_SERVER["REQUEST_METHOD"] === "POST"){
		try{
			//init
			$title_s = "";
			$detail = "";
			$comment = "";
			$mb_title = "";
			
			//set
			if(!isset($_POST["title"]) || !isset($_POST["comment"]) || !isset($_POST["detail"]) || !isset($_POST["title_s"])){
				page_error(400);
			}
			
			//これはSQLに入れない
			$title_s = $_POST["title_s"];
			$detail = $_POST["detail"];
			$comment = $_POST["comment"];
			$mb_title = $_POST["title"];
			
			check_csrf();
			
			//値チェック(POST)
			if(mb_strlen($comment,"UTF-8") > 2500 || mb_strlen($comment,"UTF-8") < 20){
				throw new InputMissException("コメントの文字数が不適切です。");
			}
			if(mb_strlen($_POST["title"],"UTF-8") > 50 || mb_strlen($_POST["title"],"UTF-8") < 4){
				throw new InputMissException("タイトルは4文字～50文字の範囲内に収めてください。");
			}
			
			$dbh = getPDO();
			try{
				$dbh->beginTransaction();
				$stmt = $dbh->prepare("UPDATE enterprise_comment SET text = ?, title = ? WHERE id = ? AND user_id = ?;");
				$stmt->execute(array($comment,$mb_title,$id,$user_id));
				
				//記事IDの取得
				$stmt = $dbh->prepare("SELECT article_id FROM enterprise_comment WHERE id = ? AND user_id = ?");
				$stmt->execute(array($id,$user_id));
				$db = $stmt->fetch(PDO::FETCH_ASSOC);
				if(!$db){
					page_error(403);
				}
				$article_id = $db["article_id"];
				
				$dbh->commit();
				header("location:show.php?id=".$article_id);
			}catch(Exception $e_2){
				$dbh->rollback();
				throw new Exception($e_2->getMessage());
			}
		}catch(InputMissException $e){
			$e->stackTracePage();
		}catch(Exception $e){
			page_fatal_error("comment_modify/73/".$e->getMessage());
		}
	}else{
		//コメントの確認
		$dbh = getPDO();
		$stmt = $dbh->prepare("SELECT text,title,article_id FROM enterprise_comment WHERE id = ? AND user_id = ?");
		
		try{
			$stmt->execute(array($id,$user_id));
			$db = $stmt->fetch(PDO::FETCH_ASSOC);
			if(!$db){
				throw new InputMissException("page not found");
			}
			
			$comment = $db["text"];
			$mb_title = $db["title"];
			$article_id = (int)$db["article_id"];
			
			$stmt = $dbh->prepare("SELECT title,detail FROM enterprise_review WHERE id = ?");
			$stmt->execute(array($article_id));
			
			$db = $stmt->fetch(PDO::FETCH_ASSOC);
			if(!$db){
				page_error(403);
			}
			
			$title_s = $db["title"];
			$detail = $db["detail"];
		}catch(InputMissException $e){
			$e->stackTracePage();
		}catch(Exception $e){
			page_fatal_error("comment_modify/105/".$e->getMessage());
		}
	}
?>

<?php
	require_once($_SERVER['DOCUMENT_ROOT']."/template/header_tkool.php");
?>

<div id="content">
	<h1>コメントの修正</h1>
	<table>
		<tr><td>名称</td><td><?php echo h($title_s); ?></td></tr>
		<tr><td>概要</td><td><?php echo h($detail); ?></td></tr>
	</table>
	<?php echo h($error_message); ?>
	修正する情報を記述してください。
	<br><br>
	<form method="post">
		コメント情報(20～2500文字)：<br>
		<textarea name="comment" maxlength=2500 minlength=20 required><?php echo h($comment); ?></textarea>
		タイトル(4～50文字)：<br>
		<input type="text" name="title" max=100 min=0 value="<?php echo h($mb_title); ?>"></input><br>
		<input type="hidden" name="authenticate_id" value="<?php echo h(get_authenticate_id()); ?>"></input>
		<input type="hidden" name="title_s" value="<?php echo h($title_s); ?>"></input>
		<input type="hidden" name="detail" value="<?php echo h($detail); ?>"></input>
		<input type="submit" value="コメントの変更"></input>
	</form>
</div>

<?php
	require_once($_SERVER['DOCUMENT_ROOT']."/template/footer_tkool.php");
?>