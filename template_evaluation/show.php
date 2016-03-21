<?php
	function geturl_search($option,$order,$text,$isshow_and){
		$sr = "show.php?";
		$text_modified = false;
		if($option !== ""){
			$sr .= "option=".urlencode($option);
			$text_modified = true;
		}
		if($order !== ""){
			if($text_modified)$sr .= "&";
			$sr .= "order=".urlencode($order);
			$text_modified = true;
		}
		if($text !== ""){
			if($text_modified)$sr .= "&";
			$sr .= "text=".urlencode($text);
			$text_modified = true;
		}
		$t = $isshow_and?($text_modified?"&":"?"):"";
		return h($text_modified ? $sr : "show.php").$t;
	}
	require_once($_SERVER['DOCUMENT_ROOT']."/safe/securimage.php");
	require_once($_SERVER['DOCUMENT_ROOT']."/module/common.php");
	
	//IDがあれば記事、なければ検索
	try{
		//変数の初期化
		$now_page = 1;
		$order = "";
		$option = "";
		$text = "";
		
		//ＤＢ
		$dbh = getPDO();
		$is_login = login_check();
		if($is_login){
			$user_id = (int)getLoginID();
		}
		
		//タイプを見る
		if(!isset($template->type) && style_check($template->type)){
			page_error(404);
		}
		
		//記事番号(id)のセット
		$id_set = true;
		if(isset($_GET["id"])){
			$id = (int)$_GET["id"];
		}else{
			$id_set = false;
		}
		
		//idがある状態でなおかつログイン中の場合、ニュースから記事を消す
		if($id_set && $is_login){
			$stmt = $dbh->prepare("DELETE FROM news WHERE user_id = ? AND article_id = ? AND type = ? AND already_read = ?;");
			$stmt->execute(array($user_id,$id,$template->type,1));
		}
		
		//順序検定
		$sql_order = "ORDER BY id DESC";
		if(isset($_GET["order"])){
			$order = $_GET["order"];
			if($_GET["order"] === "reverse"){
				$sql_order = "ORDER BY id ASC";
			}else if($_GET["order"] === "evaluation"){
				$sql_order = "ORDER BY evaluate_score,id DESC";
			}
		}
		if($id_set){
			$stmt = $dbh->prepare("SELECT title,evaluate_score,evaluate_number,information_fromurl,time,name_show,user_id,detail,img_exist,pv,totalpoint,point FROM ".$template->type."_review WHERE id = ?;");
			$stmt->execute(array($_GET["id"]));
			$result = $stmt->fetch(PDO::FETCH_ASSOC);
			if($result){
				$article = new Article_Detail($result);
			}else{
				page_error(404);
			}
			
			//pv数の加算
			if(!$is_login || $article->user_id !== $user_id){
				$stmt = $dbh->prepare("UPDATE ".$template->type."_review SET pv = ? WHERE id = ?;");
				$stmt->execute(array((int)($article->pv)+1,$id));
				$article->pv ++;
			}
			
			//評価情報,コメント数の取得
			$stmt = $dbh->prepare("SELECT COUNT(id) FROM ".$template->type."_comment WHERE article_id = ?;");
			$stmt->execute(array($id));
			if($result = $stmt->fetch(PDO::FETCH_ASSOC)){
				$count = (int)$result["COUNT(id)"];
			}
			$page_num = 1+(int)(($count-1)/5);
			
			//ページ数の取得
			if(isset($_GET["page"]) && preg_match("/^[1-9][0-9]*$/",$_GET["page"]) && (int)$_GET["page"] <= $page_num){
				$now_page = (int)$_GET["page"];
			}else{
				$now_page = 1;
			}
			
			//コメント情報
			$stmt_comment = $dbh->prepare("SELECT text,user_id,id,time,totalpoint,point,title FROM ".$template->type."_comment WHERE article_id = ? ".$sql_order." LIMIT 5 OFFSET ?;");
			$stmt_comment->execute(array($id,$now_page*5-5));
			
			//コメント投稿可能か
			if($is_login){
				$stmt = $dbh->prepare("SELECT COUNT(id) FROM ".$template->type."_comment WHERE article_id = ? AND user_id = ?");
				$stmt->execute(array($id,$user_id));
				if($db = $stmt->fetch(PDO::FETCH_ASSOC)){
					$count = (int)$db["COUNT(id)"];
					if($count > 0){
						$comment_submittable = false;
					}else{
						$comment_submittable = true;
					}
				}else{
					page_fatal_error("show.php/133:データベースが故障しているぞ");
				}
			}else{
				$comment_submittable = false;
			}
			
			//その他タイトル情報
			$title = $template->title."のレビュー/評価";
			$input_meta_description = $template->meta_description;
			$input_meta_keyword = $template->meta_keyword;
		}else{
			//検索ワードのチェック (レンタルサーバー対策用：蔚を3つ付け加えること)
			$split_text = "";
			$arr = array();
			if(isset($_GET["option"])){
				if($_GET["option"] === "キーワード検索" && isset($_GET["text"]) && mb_strlen($_GET["text"],"UTF-8") > 0 && mb_strlen($_GET["text"],"UTF-8") < 45){
					$text = $_GET["text"];
					$option = $_GET["option"];
					$tmp_obj = new Search_Word_Show($_GET["text"]);
					$split_text = " WHERE match(search_word_noroot) against(? in boolean mode) ";
					$arr = array($tmp_obj->getNorootSearchText());
				}
			}
			
			//ページ数のカウント処理
			$stmt = $dbh->prepare("SELECT COUNT(id) AS cnt FROM ".$template->type."_review ".$split_text);
			$stmt->execute($arr);
			$result = $stmt->fetch(PDO::FETCH_ASSOC);
			if(!$result){
				page_fatal_error("show.php/209:データベースが故障しているぞ");
			}
			
			$total_review_num = $result["cnt"];
			$page_num = 1+(int)(($result["cnt"]-1)/10);
			
			//ページ数の取得
			if(isset($_GET["page"]) && preg_match("/^[1-9][0-9]*$/",$_GET["page"]) && (int)$_GET["page"] <= $page_num){
				$now_page = (int)$_GET["page"];
			}
			
			//コメントの取得(一部でよい)
			$stmt_search = $dbh->prepare("SELECT evaluate_score,evaluate_number,id,title,detail_omit,time FROM ".$template->type."_review ".$split_text." ".$sql_order." LIMIT 10 OFFSET ?;");
			$arrf = array_merge($arr,array($now_page*10-10));
			$stmt_search->execute($arrf);
			$title = $template->title."評価レビュー";
		}
	}catch(InputMissException $e){
		$e->stackTracePage();
	}catch(Exception $e){
		page_fatal_error("review_create.php-81/".$e->getMessage());
	}
?>
<?php
	require_once($_SERVER['DOCUMENT_ROOT']."/template/header_tkool.php");
	$auto_id = get_authenticate_id();
?>

<style>
/*固有CSS(PC,SPとも共通の内容に限る)*/
#button_judge_ok{
	padding:10px;
	padding-left:50px;
	padding-right:5px;
	text-align:right;
	font-size:20px;
	border:1px solid #000;
	cursor:pointer;
	background-image:url("/file_img/aro_sansei.png");
	background-repeat: no-repeat;
}
#button_judge_ng{
	padding:10px;
	padding-left:50px;
	padding-right:5px;
	text-align:right;
	font-size:20px;
	border:1px solid #000;
	cursor:pointer;
	background-image:url("/file_img/aro_deny.png");
	background-repeat: no-repeat;
}
#button_judge_ok:hover{
	background-color:#88D;
}
#button_judge_ng:hover{
	background-color:#88D;
}

/*
	コメント領域
*/
#comment_space{
	padding:5px;
	margin-top:10px;
	background-color:#D8F8B8;
}
#comment_space_h{
	padding:5px;
	background-color:#B8E844;
}
#comment_space_t{
	padding:5px;
}

/*
	記事スペース１
*/
#showpart{
	background-color:#FFF;
	padding-top:10px;
	padding-left:15px;
	padding-right:15px;
	padding-bottom:25px;
}
#showpart_header{
	
}
#showpart_container{
	padding:10px;
	background-color:#F5F5F5;
	border-bottom:2px solid #DDD;
	border-left:2px solid #DDD;
	border-right:2px solid #DDD;
}
#showpart_img{
	float:left;
	width:200px;
	height:200px;
	position: relative;
}
#showpart_img img{
	max-width:160px;
	max-height:160px;
	display:block;
	position: absolute;
	margin:auto;
	top: 0;
	right: 0;
	bottom: 0;
	left: 0;
}
#showpart_table{
	width:270px;
	vertical-align: middle;
	float:right;
	display: table-cell;
	position: relative;
}
#showpart_mainpart{
	clear:both;
}



</style>
<input type="hidden" id="authenticate_id" value="<?php echo $auto_id; ?>"></input>
<script src="/template/jquery-1.12.0.min.js" type="text/javascript"></script>
<script>
	function send_eval(isgood,id,option){
		var s = <?php echo $is_login?"true":"false" ?>;
		if(!s){
			window_show_login();
			return;
		}
		$.ajax({
			url:"getuser_eval.php?level="+option+"&id="+id,
			method:"POST",
			data:{eval:isgood?"1":"0",authenticate_id:$("#authenticate_id").val()},
			success:modify_evaluation,
			timeout:50000,
			error:error_call
		});
	}
	function modify_evaluation(e){
		/*
			返信ヘッダ：id,parent or child,good,bad,your_evaluate
		*/
		console.log(e);
		try{
			var strarray = e.split("#");
			var isparent = strarray[1] === "parent";
			var id = Number(strarray[0]);
			var good = Number(strarray[2]);
			var bad = Number(strarray[3]);
			var your_evaluate = Number(strarray[2]);
			
			var app = ["4px solid #000","1px solid #000"];
			if(isNaN(id) || isNaN(good) || isNaN(bad) || isNaN(your_evaluate)){
				throw "ERROR";
			}
			if(isparent)id="parent";
			
			if(your_evaluate === 1){
				$(".deny_"+id).css("border",app[1]);
				$(".agree_"+id).css("border",app[0]);
			}else if(your_evaluate === 0){
				$(".deny_"+id).css("border",app[0]);
				$(".agree_"+id).css("border",app[1]);
			}else{
				throw "ERROR";
			}
			
			$(".agree_"+id).html(String(good));
			$(".deny_"+id).html(String(bad));
		}catch(e){
			window_show_error();
		}
		
	}
	function error_call(){
		var str = "エラーが発生しました";
	}
</script>
	<form action="show.php">
		<input type="text" name="text" minlength=2 maxlength=100 value="<?php echo h(isset($_GET["search"])? $_GET["search"]: ""); ?>" required></input>
		<input type="submit" value="検索" name="option"></input>
	</form>
	<?php
		if($id_set){
			?>
				<?php
					if($article->name_show === 1){
						?>
							<a href="/user.php?id=<?php echo h($article->user_id); ?>" id="mainpart_header">投稿者情報</a>
							<a href="/user.php?page=1&id=<?php echo h($article->user_id); ?>" id="mainpart_header">投稿レビュー</a>
						<?php
					}
					if($is_login){
						echo '<a href="add_bookmark.php?id='.h($id).'" id="mainpart_header">ブックマーク</a>';
					}else{
						echo '<a onClick="window_show_login()">ブックマーク</a>';
					}
				?>
				<!--STATUS AREA-->
				<div id="showpart">
					<h1><?php echo h($article->title); ?>のレビュー・紹介</h1>
					<div id="showpart_container">
						<div id="showpart_header">
							<div id="showpart_img">
							<?php
								$img_tmp = "";
								if($article->img_exist === "png" || $article->img_exist === "jpg" || $article->img_exist === "gif"){
									$img_tmp = $article->img_exist;
								}
								if($img_tmp !== ""){
									echo "<a href='img/".$id."image.".$img_tmp."' target='_blank' class=\"no-hover\"><img src='img/".$id."image.".$img_tmp."' alt='img/".$id."image.".$img_tmp."'/></a>";
								}else{
									echo '<img src="/file_img/noimage.png"/>';
								}
								unset($img_tmp);
							?>
							</div>
							<div id="showpart_table">
								<table width="100%">
									<tr>
										<td>評価</td>
										<td><?php echo h($article->evaluate_score); ?>/100</td>
									</tr>
									<tr>
										<td>閲覧数</td>
										<td><?php echo h($article->pv); ?></td>
									</tr>
									<tr>
										<td>投稿数</td>
										<td><?php echo h($count); ?></td>
									</tr>
								</table>
							</div>
						</div>
						<div id="showpart_mainpart">
							<h2>レビュー</h2>
							<div id="content_article_detail">
							<?php echo nl2br(h($article->detail)) ?>
							<br>
								<!--
									キャラ評価
								-->
								<?php
									try{
										$vote_approve = -1;
										if($is_login){
											$stmt = $dbh->prepare("SELECT eval_point FROM enterprise_log_eval_parent WHERE article_id = ? AND user_id = ?;");
											$stmt->execute(array((int)$id,$user_id));
											if($data = $stmt->fetch(PDO::FETCH_ASSOC)){
												$vote_approve = (int)$data["eval_point"];
											}
										}
										echo '<br><span id="button_judge_ok" class="agree_parent" ';
										if($vote_approve === 1){
											echo ' style="border:4px solid #000;" ';
										}
										echo 'onClick="send_eval(true,'.h($id).',\'parent\')">';
										echo h($article->point).'</span>&nbsp;';
										
										//低評価部分
										echo '<span id="button_judge_ng" class="deny_parent" ';
										if($vote_approve === 0){
											echo ' style="border:4px solid #000;" ';
										}
										echo 'onClick="send_eval(false,'.h($id).',\'parent\')">';
										echo h((string)($article->totalpoint-$article->point)).'</span>';
										
										//違反報告部分
										echo '<a id="violation" href="/violation_recall.php?id='.$id.'&level=review&type='.$template->type.'">違反報告</a>';
									}catch(Exception $e){
										page_fatal_error("show.php/499:レビューの評価情報の取得失敗");
									}
									$vote_approve = 1;
								?>
							</div>
						</div>
						<?php
							if($article->information_fromurl !== ""){
								echo '<h2>脚注/参考URL</h2>';
								draw_url($article->information_fromurl);
							}
						?>
						<?php
							if($is_login && $user_id === $article->user_id){
								echo '<h2>記事の編集関連</h2><a id="modify" href="review_modify.php?id='.$id.'">修正</a>&nbsp;';
								echo '<a id="delete_bookmark" href="delete.php?style=review&id='.$id.'">削除</a>&nbsp;';
								echo '<a id="img_modify" href="review_filechange.php?id='.$id.'">画像削除/差し換え</a>';
							}
						?>
					</div>
					<a href="/howto.php#eval">※評価の意味等はデータの見方を閲覧してください</a><br><br>
					<!--
						コメント関連
					-->
					<h2>コメント</h2>
					<?php
						while($comment_data = $stmt_comment->fetch(PDO::FETCH_ASSOC)){
							//コメント履歴の取得
							$username = "";
							$stmt = $dbh->prepare("SELECT nickname FROM userdata WHERE id = ?;");
							$stmt->execute(array($comment_data["user_id"]));
							
							if($result_sub = $stmt->fetch(PDO::FETCH_ASSOC)){
								$username = $result_sub["nickname"];
								if($username === ""){
									$username = "ユーザー名なし";
								}
							}else{
								page_fatal_error("show.php/528:エラーが起きたぞ！ユーザー名が取得されてない！");
							}
							
							echo '<div id="comment_space">';
							echo '<div id="comment_space_h">ユーザー名：<a href="/user.php?id='.h($comment_data["user_id"]).'">'.h($username).'</a><br>タイトル：'.h($comment_data["title"]).'<br>投稿日時：'.h($comment_data["time"])."</div><div id='comment_space_t'>";
							echo nl2br(h($comment_data["text"]))."</div>";
							
							$vote_approve = -1;
							
							if($is_login){
								$stmt = $dbh->prepare("SELECT eval_point FROM ".$template->type."_log_eval_child WHERE user_id = ? AND article_id = ?;");
								$stmt->execute(array($user_id,$comment_data["id"]));
								if($result_sub = $stmt->fetch(PDO::FETCH_ASSOC)){
									$vote_approve = (int)$result_sub["eval_point"];
								}
							}
							
							//高評価部分
							echo '<span id="button_judge_ok" class="agree_'.h($comment_data["id"]).'" ';
							if($vote_approve === 1){
								echo ' style="border:4px solid #000;" ';
							}
							echo 'onClick="send_eval(true,'.h($comment_data["id"]).',\'child\')">';
							echo h($comment_data["point"]).'</span>&nbsp;';
							
							//低評価部分
							echo '<span id="button_judge_ng" class="deny_'.h($comment_data["id"]).'" ';
							if($vote_approve === 0){
								echo ' style="border:4px solid #000;" ';
							}
							echo 'onClick="send_eval(false,'.h($comment_data["id"]).',\'child\')">';
							echo h((string)((int)$comment_data["totalpoint"]-(int)$comment_data["point"])).'</span>';
							
							//違反報告
							echo '<a id="violation" href="/violation_recall.php?id='.h($comment_data["id"]).'&level=comment&type='.$template->type.'">違反報告</a></div>';
						}
						echo will_paginate('<a href="show.php?id='.$id.'">',$now_page,$page_num,"[","]");
					?>
					<?php
					if($is_login){
						?>
							<h3>コメント</h3>
							<form method="post" action="comment_send.php" id="form_space">
								タイトル(4文字～)：<br>
								<input type="hidden" name="article_id" value="<?php echo h($id); ?>"></input>
								<input type="text" name="title" minlength=4 maxlength=50 required></input><br>
								<input type="hidden" name="authenticate_id" value="<?php echo $auto_id; ?>"></input>
								コメント(5～2500文字)：<br>
								<textarea name="comment" maxlength=2500 minlength=5 required></textarea><br>
								画像認証：<br>
								<?php 
									echo Securimage::getCaptchaHtml();
								?>
								<input type="submit" value="送信"></input>
							</form>
						<?php
					}else{
						echo "<h3>投稿</h3>あなたもE2BCに登録して投稿してみませんか？";
					}
					
					if($is_login){
						?>
						<h3>投稿コメントの修正</h3>
						<?php
						$stmt_comment_submit = $dbh->prepare("SELECT * FROM ".$template->type."_comment WHERE article_id = ? AND user_id = ?");
						$stmt_comment_submit->execute(array($id,$user_id));
						$comment_exist = false;
						while($data = $stmt_comment_submit->fetch(PDO::FETCH_ASSOC)){
							$comment_exist = true;
							?>
							<div id='comment_space'>
								<div id='comment_space_h'>
								タイトル：<?php echo h($data["title"]); ?><br>
								投稿時間：<?php echo h($data["time"]); ?><br></div>
								<div id='comment_space_t'><?php echo h($data["text"]); ?></div>
								<a id='modify' href='comment_modify.php?id="<?php echo h($data["id"]); ?>"'>修正</a>&nbsp;&nbsp;
								<a id='delete_bookmark' href='delete.php?style=comment&id=".<?php echo h($data["id"]); ?>."'>削除</a>
							</div>
							<?php
						}
						if(!$comment_exist){
							echo "コメントが投稿されていません。";
						}
					}
					?>
					</div>
			<?php
		}else{
			?>
			<div id="showpart">
				<h1><?php echo $template->title; ?>リスト</h1>
				検索件数：<?php echo h($total_review_num); ?>件&nbsp;&nbsp;
				<?php
					if($total_review_num !== 0){
						?>
							<a href="<?php echo geturl_search($option,"",$text,false); ?>">[新しい記事優先]</a>
							<a href="<?php echo geturl_search($option,"reverse",$text,false); ?>">[古い記事優先]</a>
							<a href="<?php echo geturl_search($option,"evaluation",$text,false); ?>">[評価順]</a><br>
							<?php 
								will_paginate(geturl_search($option,$order,$text,true),$now_page,$page_num,"[","]")."<br>";
								while($db = $stmt_search->fetch(PDO::FETCH_ASSOC)){
									getReviewWindowText($db,false);
								}
								will_paginate(geturl_search($option,$order,$text,true),$now_page,$page_num,"[","]")."<be>";
							?>
						<?php
					}else{
						?>
							<br>記事はありません。書いてみませんか？<br>
						<?php
						if($is_login){
							echo '<a href="review_create.php">記事を書く</a>';
						}else{
							echo '<a onClick="window_show_login()">記事を書く</a>';
						}
					}
				?>
				<br><br>
				<h2>ランダム選択</h2>
				<?php
					require_once("randshow.php");
				?>
			</div>
		<?php
		}
	?>
<?php
	require_once($_SERVER['DOCUMENT_ROOT']."/template/footer_tkool.php");
?>