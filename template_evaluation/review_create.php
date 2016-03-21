<?php
	require_once($_SERVER['DOCUMENT_ROOT']."/module/common.php");
	
	//initialize
	$error_message = "";
	$review_text = "";
	$detail = "";
	$ftitle = "";
	$name_show = "1";
	$evaluate = 50;
	$information_fromurl = "";
	
	if(!login_check()){
		header("location:login.php");
		exit;
	}
	$user_id = getLoginID();
	
	if($_SERVER["REQUEST_METHOD"] === "POST"){
		try{
			$review_text = check_review_text();
			$information_fromurl = check_url_info();
			$evaluate = check_evaluate();
			$name_show = check_nameshow();
			$ftitle = check_title();
			$fileinfo = null;
			$review_text_omit = mb_substr($review_text,0,100,"UTF-8");
			if($review_text_omit !== $review_text){
				$review_detail_omit .= "...";
			}
			$happy_maker = new Search_Word($ftitle."#".$review_text);
			$review_search_word = $happy_maker->getSearchText();
			$review_search_word_noroot = $happy_maker->getNorootSearchText();
			
			try{
				$fileinfo = file_check("imgfile_data");
			}catch(RuntimeException $e){
				throw new InputMissException("アップロードできるファイルはjpg,png,gifで1MBまでです。");
			}
			
			check_csrf();
			$dbh = getPDO();
			try{
				$dbh->beginTransaction();
				$stmt = $dbh->prepare("INSERT ".$template->type."_review (user_id,title,detail,detail_omit,information_fromurl,img_exist,evaluate_score,name_show,
				search_word,search_word_noroot,type) VALUES (?,?,?,?,?,?,?,?,?,?,?);");
				$arr = array((int)$user_id,$ftitle,$review_text,$review_text_omit,$information_fromurl,
				$fileinfo["extension"] === null ? "" : $fileinfo["extension"] ,(int)$evaluate,(int)$name_show,
				$review_search_word,$review_search_word_noroot,$template->type);
				$stmt->execute($arr);
				
				$insert_id = $dbh->lastInsertId("id");
				
				if(preg_match("/^[1-9]+[0-9]*$/",(string)$insert_id)){
					//画像ファイルの名称変更
					if($fileinfo){
						if(move_uploaded_file($_FILES["imgfile_data"]["tmp_name"],"img/".$insert_id."image.".$fileinfo["extension"]) ){
							chmod($template->type."/img/".$insert_id."image.".$fileinfo["extension"],0644);
						}else{
							throw new InputMissException("ファイルのアップロードに失敗しました。");
						}
					}
					
					renew_sitemap($id,$type,$dbh);
					
					$dbh->commit();
					
					header("location:show.php?id=".$insert_id);
				}else{
					throw new InputMissException("IDの取得に失敗しました。");
				}
			}catch(RuntimeException $e){
				$dbh->rollback();
				$ee = new InputMissException("ファイルの処理に失敗しました。拡張子やファイルサイズを確かめて再度アップロードしてください。");
				$ee->stackTracePage();
			}catch(InputMissException $e){
				$dbh->rollback();
				$e->stackTracePage();
			}catch(Exception $e){
				$dbh->rollback();
				page_fatal_error("review_create.php-81/".$e->getMessage());
			}
		}catch(Exception $e){
			$error_message = $e->getMessage();
		}
	}
?>
<?php
	$title = "レビュー記事の投稿";
	require_once($_SERVER['DOCUMENT_ROOT']."/template/header_tkool.php");
?>
<div id="content">
	<form method="post" enctype="multipart/form-data">
		<h1><?php echo $template->title; ?>レビューの投稿</h1>
		<div>
			<?php if($error_message !== "")echo nl2br(h($error_message)); ?>
		</div>
		タイトル(4～50文字：略称ではなく正式名称でお願いします)：<br>
		<input type="text" name="title" value="<?php echo h($ftitle); ?>" minlength=4 maxlength=50 required></input><br>
		評価テキスト(20～2500文字)：<br>
		<textarea id="expand_area" name="review_text" maxlength=2500 minlength=20 required><?php echo h($review_text); ?></textarea><br>
		<input type="button" onClick="exp_textarea()" value="枠拡張"></input><br>
		URL(0～1500文字)：<br>
		<textarea name="information_fromurl" value="<?php echo h($information_fromurl); ?>" maxlength=50></textarea><br>
		評価(0～100)：<br>
		<input type="number" name="evaluate" value=50 min=0 max=100 required></input><br>
		評価基準は、普通が50点、これ以上悪いものがいかなる場合でも存在しない場合は0点、その逆を100点とすること。<br>
		<br>名前表示：<br>
		<input type="radio" name="name_show" value="1" <?php if($name_show === "1")echo "checked='checked'"; ?> >ユーザー名を公表<br>
		<input type="radio" name="name_show" value="0" <?php if($name_show === "0")echo "checked='checked'"; ?> >ユーザー名を公表しない<br>
		画像ファイル(jpg,gif,png 1MBまで)：<br>
		<input type="file" name="imgfile_data"></input><br>
		※使用不可文字：<?php h('\\0/*:<>;"|?\\') ?><br>
		<input type="hidden" name="authenticate_id" value="<?php echo h(get_authenticate_id()); ?>"></input>
		<input type="submit" value="確認"></input>
	</form>
</div>
<script type="text/javascript" src="/template/jquery-1.12.0.min.js"></script>
<script type="text/javascript" src="/template/scrollbar_autoadjust.js"></script>
<?php
	require_once($_SERVER['DOCUMENT_ROOT']."/template/footer_tkool.php");
?>