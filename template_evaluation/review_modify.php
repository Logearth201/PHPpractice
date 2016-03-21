<?php
	require_once($_SERVER['DOCUMENT_ROOT']."/module/common.php");
	require_once($_SERVER['DOCUMENT_ROOT']."/safe/securimage.php");
	require_once($_SERVER['DOCUMENT_ROOT']."/module/review_send.php");
	$title = "送信の確認";
	
	$error_message = "";
	if(!login_check()){
		header("location:login.php");
		exit;
	}
	
	$id = 0;
	$user_id = (int)getLoginID();
	
	$review_title = "";
	$review_evaluate = 50;
	$review_detail = "";
	$review_information_fromurl = "";
	$review_name_show = 1;
	
	if($_SERVER["REQUEST_METHOD"] === "POST"){
		try{
			check_csrf();
			
			if(!isset($_GET["id"])){
				throw new InputMissException("取得しようとしたIDが正しくありません。");
			}else{
				$id = (int)$_GET["id"];
			}
			$review_title = check_title();
			$review_evaluate = (int)check_evaluate();
			$review_detail = check_review_text();
			$review_information_fromurl = check_url_info();
			$review_name_show = check_nameshow();
			$review_detail_omit = mb_substr($review_detail,0,100,"UTF-8");
			
			$happy_maker = new Search_Word($review_title."#".$review_detail);
			$review_search_word = $happy_maker->getSearchText();
			$review_search_word_noroot = $happy_maker->getNorootSearchText();
			
			if($review_detail_omit !== $review_detail){
				$review_detail_omit .= "...";
			}
			
			$dbh = getPDO();
			try{
				$dbh->beginTransaction();
				$stmt = $dbh->prepare("UPDATE ".$template->type."_review SET title = ?, evaluate_score = ?, detail = ?, information_fromurl = ?, name_show = ?, detail_omit = ?, search_word = ?, search_word_noroot = ? WHERE user_id = ? AND id = ?;");
				$stmt->execute(array($review_title,$review_evaluate,$review_detail,$review_information_fromurl,(int)$review_name_show,$review_detail_omit,$review_search_word,$review_search_word_noroot,$user_id,$id));
				$dbh->commit();
				header("location:show.php?id=".$id);
			}catch(Exception $e){
				$dbh->rollback();
				throw new Exception($e->getMessage());
			}
		}catch(InputMissException $e){
			$e->stackTracePage();
		}catch(Exception $e){
			page_fatal_error("review_create.php-81/".$e->getMessage());
		}
	}else{
		try{
			if(!isset($_GET["id"])){
				throw new InputMissException("取得しようとしたIDが正しくありません。");
			}else{
				$id = (int)$_GET["id"];
			}
			$dbh = getPDO();
			$stmt = $dbh->prepare("SELECT title,evaluate_score,detail,name_show,information_fromurl FROM ".$template->type."_review WHERE id = ? AND user_id = ?;");
			$stmt->execute(array($id,$user_id));
			if($data = $stmt->fetch(PDO::FETCH_ASSOC)){
				$review_title = $data["title"];
				$review_evaluate = $data["evaluate_score"];
				$review_detail = $data["detail"];
				$review_name_show = $data["name_show"];
				$review_information_fromurl = $data["information_fromurl"];
			}else{
				page_error(403);
			}
		}catch(InputMissException $e){
			$e->stackTracePage();
		}catch(Exception $e){
			page_fatal_error("review_create.php-81/".$e->getMessage());
		}
	}
	
	require_once($_SERVER['DOCUMENT_ROOT']."/template/header_tkool.php");
?>

<div id="content">
	<h1><?php echo $template->title; ?>レビューの修正・追記</h1>
	<form method="post">
		記事タイトル：<br>
		<input type="text" name="title" value="<?php echo h($review_title); ?>" maxlength=50 minlength=4 required></input><br>
		記事内容：<br>
		<textarea id="expand_area" name="review_text" maxlength=2500 minlength=20 required><?php echo h($review_detail); ?></textarea><br>
		<input type="button" onClick="exp_textarea()" value="枠拡張"></input><br>
		評価：<br>
		<input type="number" name="evaluate" value="<?php echo h($review_evaluate) ?>" max=100 min=0 required></input><br>
		
		名前の表示：<br>
		<input type="radio" name="name_show" value="1" <?php if($review_name_show !== 0)echo "checked='checked'"; ?> >ユーザー名を公表<br>
		<input type="radio" name="name_show" value="0" <?php if($review_name_show === 0)echo "checked='checked'"; ?> >ユーザー名を公表しない<br>
		URL(0～1500文字)：<br>
		<textarea name="information_fromurl" maxlength=1500><?php echo h($review_information_fromurl); ?></textarea><br>
		<input type="hidden" name="authenticate_id" value="<?php echo h(get_authenticate_id()); ?>"></input>
		<input type="button" value="戻る" onClick="javascript:history.back()"></input>
		<input type="submit" value="送信"></input>
	</form>
</div>
<script type="text/javascript" src="/template/jquery-1.12.0.min.js"></script>
<script type="text/javascript" src="/template/scrollbar_autoadjust.js"></script>
<?php
	require_once($_SERVER['DOCUMENT_ROOT']."/template/footer_tkool.php");
?>