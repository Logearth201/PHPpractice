<?php
	//user_idの取得
	require_once($_SERVER['DOCUMENT_ROOT']."/module/common.php");
	require_once($_SERVER['DOCUMENT_ROOT']."/module/review_send.php");
	
	$logined = login_check();
	if(!$logined){
		header("location:login.php");
		exit;
	}
	$user_id = getLoginID();
	
	//idチェック
	if(!isset($_GET["id"]) || !preg_match("/^[0-9]+[1-9]*$/",$_GET["id"])){
		page_error(404);
	}
	$id = $_GET["id"];
	
	//optionの選択(comment or data)
	if(!isset($_GET["style"]) && ($_GET["style"] !== "review" || $_GET["style"] !== "comment")){
		page_error(404);
	}
	$style = $_GET["style"];
	
	if($_SERVER["REQUEST_METHOD"] === "POST"){
		check_csrf();
		//クエリ文の発行
		$dbh = getPDO();
		try{
			$dbh->beginTransaction();
			if($style === "comment"){
				$stmt = $dbh->prepare("SELECT article_id FROM ".$template->type."_".$style." WHERE id = ?;");
				$stmt->execute(array($id));
				$data = $stmt->fetch(PDO::FETCH_ASSOC);
				if(!$data){
					throw new InputMissException("該当するコメントがありません");
				}
				$back_to_id = (int)$data["article_id"];
			}
			$stmt = $dbh->prepare("DELETE FROM ".$template->type."_".$style." WHERE id = ? AND user_id = ?;");
			$stmt->execute(array($id,$user_id));
			
			if($style === "review"){
				//png,jpg,gifの削除
				if(file_exists("img/".$id."image.jpg"))unlink("img/".$id."image.jpg");
				if(file_exists("img/".$id."image.png"))unlink("img/".$id."image.png");
				if(file_exists("img/".$id."image.gif"))unlink("img/".$id."image.gif");
				
				//サイトマップの更新
				$data = prep_write_sitemap_text($id,$dbh,$template->type,$id);
				write_sitemap($data);
			}
			
			$dbh->commit();
			
			if($style === "comment"){
				header("location:delete_completed.php?id=".$back_to_id."&style=".$style);
			}else{
				header("location:delete_completed.php?style=".$style);
			}
		}catch(InputMissException $e){
			$e->stackTracePage();
		}catch(Exception $e){
			page_fatal_error("comment_send.php//".$e->getMessage());
		}
	}else{
		$dbh = getPDO();
		try{
			if($style !== "review"){
				$stmt = $dbh->prepare("SELECT title,text FROM ".$template->type."_".$style." WHERE id = ? AND user_id = ?;");
			}else{
				$stmt = $dbh->prepare("SELECT title,detail FROM ".$template->type."_".$style." WHERE id = ? AND user_id = ?;");
			}
			$stmt->execute(array($id,$user_id));
			$data = $stmt->fetch(PDO::FETCH_ASSOC);
			if(!$data){
				page_error(403);
			}
		}catch(InputMissException $e){
			$e->stackTracePage();
		}catch(Exception $e){
			page_fatal_error("comment_send.php//".$e->getMessage());
		}
	}
?>

<?php
	require_once($_SERVER['DOCUMENT_ROOT']."/template/header_tkool.php");
?>

<div id="content">
	<?php
	if($_SERVER["REQUEST_METHOD"] === "POST"){
		?>
			<h1>エラー</h1>
			コメント送信の途中で問題が発生しました。更新するか、ブラウザバックしてやり直してください。
			<?php echo $error_message; ?>
		<?php
	}else{
		?>
		<h1>削除の確認</h1>
		<?php
		if($style === "review"){
			?>
			あなたは<?php echo h($template->title) ?>のレビューを削除しようとしています。内容を確認して、削除する場合は「削除」を、削除しない場合はブラウザバックするか、戻るボタンを押してください。<br>
			<br><div style="padding:10px;background-color:#F8F8F8"><h2 style="text-weight:bold">タイトル</h2>
			<?php echo h($data["title"]); ?>
			<div style="padding-top:5px;margin-top:5px;"><h2 style="text-weight:bold">内容</h2>
			<?php echo nl2br(h($data["text"])) ?></div></div>
			<?php
		}else{
			?>
			$open_text .= 'あなたはコメントを削除しようとしています。内容を確認して、削除する場合は「削除」を、削除しない場合はブラウザバックするか、戻るボタンを押してください。<br>';
			<br><div style="padding:10px;background-color:#F8F8F8"><h2 style="text-weight:bold">タイトル</h2>
			<?php echo h($data["title"]); ?>
			<div style="padding-top:5px;margin-top:5px;"><h2 style="text-weight:bold">内容</h2>
			<?php echo nl2br(h($data["text"])) ?></div></div>
			<?php
		}
		
		$open_text .= '<br><br>削除した場合は復元できません。<br>続行する場合は「続行」を、それ以外の場合はブラウザバックしてください。<br>';
		$open_text .= '<form method="post">';
		$open_text .= '<input type="submit" value="続行"></input>';
		$open_text .= '<input type="button" value="戻る" onClick="javascript:history.back()"></input>';
		$open_text .= '<input type="hidden" name="authenticate_id" value='.get_authenticate_id().'></input>';
		$open_text .= '</form>';
	}
	?>
</div>

<?php
	require_once($_SERVER['DOCUMENT_ROOT']."/template/footer_tkool.php");
?>