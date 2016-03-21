<?php
	require_once("module/common.php");
	
	$login_id = -1;
	if(!login_check()){
		$login = false;
	}else{
		$login = true;
		$login_id = (int)getLoginID();
	}
	
	$id = 0;
	if(isset($_GET["id"]) && preg_match("/^[1-9]+[0-9]*$/",$_GET["id"])){
		$id = (int)$_GET["id"];
	}else{
		page_error(404);
	}
	
	$dbh = getPDO();
	$stmt = $dbh->prepare("SELECT nickname,self_introduce,year,sex,webpage FROM userdata WHERE id= ?;");
	$stmt->execute(array($id));
	$nickname = "";
	
	$db = $stmt->fetch(PDO::FETCH_ASSOC);
	if($db){
		$nickname = h($db["nickname"]);
		$introduce = $db["self_introduce"];
		$year = (int)$db["year"];
		$sex = (int)$db["sex"];
		$webpage = $db["webpage"];
	}else{
		page_error(404);
	}
	
	//記事数の取得
	$stmt = $dbh->prepare("SELECT COUNT(id) FROM enterprise_review UNION SELECT COUNT(id) FROM product_review WHERE user_id = ?;");
	$stmt->execute(array((int)$id));
	$article_num = 0;
	while($db = $stmt->fetch(PDO::FETCH_ASSOC)){
		$article_num += (int)$db["COUNT(id)"];
	}
	
	$page = 1;
	$page_num = 1;
	$is_mode_review = false;
	if(isset($_GET["page"]) && preg_match("/^[1-9]+[0-9]*$/",$_GET["page"])){
		if(!$login){
			header("location:login.php");
		}
		if($article_num > 0){
			$page_num = (int)(($article_num - 1) / 10) + 1;
			$page = min((int)$_GET["page"],$page_num);
		}
		$is_mode_review = true;
	}
	
	//記事の取得 enterprise_review.title,product_review.title
	if(!$is_mode_review){
		$stmt_article = $dbh->prepare("SELECT title,time,type,evaluate_number,detail_omit,evaluate_score,id FROM enterprise_review UNION SELECT title,time,type,evaluate_number,detail_omit,evaluate_score,id FROM product_review WHERE user_id = ? AND name_show = 1 ORDER BY time DESC LIMIT 5;");
		$stmt_article->execute(array((int)$id));
	}else{
		$stmt_article = $dbh->prepare("SELECT title,time,type,evaluate_number,detail_omit,evaluate_score,id FROM enterprise_review UNION SELECT title,time,type,evaluate_number,detail_omit,evaluate_score,id FROM product_review WHERE user_id = ? AND name_show = 1 ORDER BY time DESC LIMIT 10 OFFSET ?;");
		$stmt_article->execute(array((int)$id,(int)$page*10-10));
	}
	
	//通知数の取得
	$notice = "ERROR";
	if($login && $id === $login_id){
		$stmt = $dbh->prepare("SELECT COUNT(id) FROM news WHERE user_id = ?;");
		$stmt->execute(array($id));
		if($data = $stmt->fetch(PDO::FETCH_ASSOC)){
			$notice = (int)$data["COUNT(id)"];
		}
	}
?>

<?php
	require_once("template/header_tkool.php");
?>
<style>
	#table_list a{
		display:block;
		border: 1px solid #000;
		padding:5px;
		color:#000;
		text-decoration:none;
	}
	#table_titletext{
		font-size: 23px;
		color:#008;
	}
</style>
<a href="/user.php?id=<?php echo h($id); ?>" id="mainpart_header">投稿者情報</a>
<?php
	if($login){
		?>
			<a href="/user.php?page=1&id=<?php echo h($id); ?>" id="mainpart_header">投稿レビュー</a>
		<?php
	}else{
		?>
			<a onClick="window_show_login()">投稿レビュー</a>
		<?php
	}
	
?>
<div id="content">
<?php
	if(!$is_mode_review){
	?>
		<h1><?php echo $nickname ?>さんのマイページ</h1>
	<?php
		if($login && $id === $login_id){
			?>
				<?php
					if($notice==="ERROR"){
						echo "エラーが発生しました。更新してください。";
					}else if($notice > 0){
						echo '<a href="user_notice.php">';
						echo $notice."件のお知らせがあります";
						echo "</a>";
					}
				?><br>
				<a href="password_change.php" id="modify">ユーザー情報の変更</a>
				<a href="delete_user.php" id="delete_bookmark">ユーザー削除</a>
			<?php
		}
	?>
	<h3>ステータス</h3>
	<table width="100%">
		<tr>
			<td>ニックネーム</td>
			<td><?php echo h($nickname); ?></td>
		</tr>
		<tr>
			<td>性別</td>
			<td>
				<?php
					if($sex === -1){
						echo "未設定";
					}else if($sex === 0){
						echo "女";
					}else if($sex === 1){
						echo "男";
					}
				?>
			</td>
		</tr>
		<tr>
			<td>年齢</td>
			<td>
				<?php
					if($year < 0){
						echo '未設定';
					}else if($year < 10){
						echo ($year * 10)."～".($year*10+10);
					}
				?>
			</td>
		</tr>
		<tr>
			<td>Webページ・ブログ</td>
			<td><?php draw_url($webpage); ?></td>
		</tr>
	</table>
	<h3>紹介文</h3>
	<?php echo nl2br(h($introduce)); ?><br>
	<a id="violation" href="violation_recall.php?level=user&id=<?php echo h($id); ?>">違反報告</a>
	<?php
}
	?>
	<h2>最新<?php echo $is_mode_review ? 10 : 5; ?>件のレビュー</h2>
	<?php
		while($db = $stmt_article->fetch(PDO::FETCH_ASSOC)){
			echo getReviewWindowText($db,true);
		}
		
		if(!$is_mode_review){
			if($login){
				echo '<a href="user.php?page=1&id='.h($id).'">もっと見る</a>';
			}else{
				echo '<a onClick="login_window_show();">もっと見る</a>';
			}
		}else{
			will_paginate("user.php?id=".$id."&",$page,$page_num,"[","]");
		}
	?>
</div>
<?php
	require_once("template/footer_tkool.php");
?>