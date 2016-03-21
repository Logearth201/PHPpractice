<?php
	require_once($_SERVER['DOCUMENT_ROOT']."/module/common.php");
	//ログインチェック
	if(!login_check()){
		header("location:login.php");
		exit;
	}
	$user_id = getLoginID();
	$dbh = getPDO();
	
	//ブックマーク件数の取得
	try{
		//ページ数の取得
		$page = 1;
		$stmt = $dbh->prepare("SELECT COUNT(id) FROM bookmark WHERE user_id = ? LIMIT 10 OFFSET ".($page*5-5).";");
		$stmt->execute(array($user_id));
		$db = $stmt->fetch(PDO::FETCH_ASSOC);
		if(!$db){
			throw new InputMissException("database not functioned!");
		}
		
		//ブックマークの取得
		$stmt = $dbh->prepare("SELECT booked_id,type FROM bookmark WHERE user_id = ? LIMIT 10 OFFSET ".($page*5-5).";");
		$stmt->execute(array($user_id));
	}catch(InputMissException $e){
		$e->stackTracePage();
	}catch(Exception $e){
		page_fatal_error("bookmark.php-81/".$e->getMessage());
	}
	
	
	$title = "ブックマーク";
	require_once($_SERVER['DOCUMENT_ROOT']."/template/header_tkool.php");
?>
<style>

#bookmark{
	display:inline-block;
	height:30px;
	background-image:url("/file_img/delete.jpg");
	background-repeat: no-repeat;
	cursor:pointer;
	padding-left:28px;
	font-size:20px;
	font-weight:bold;
	margin:3px;
	border:2px dashed #888;
	text-decoration:none;
	color:#000;
}
#bookmark:hover{
	background-image:url("/file_img/delete.jpg");
	background-color:#888;
}
</style>

<div id="content">
	<h2>ブックマークリスト</h2>
	<?php
	while($db = $stmt->fetch(PDO::FETCH_ASSOC)){
		$fav_text = "";
		$article_id = $db["booked_id"];
		$type = $db["type"];
		if(style_check($type)){
			$stmt_sub = $dbh->prepare("SELECT evaluate_score,evaluate_number,id,title,detail_omit,time FROM ".$type."_review WHERE id = ?");
			$stmt_sub->execute(array((int)$article_id));
			if($db_sub = $stmt_sub->fetch(PDO::FETCH_ASSOC)){
				$evaluate_count = $db_sub["evaluate_number"];
				$evaluate_score = $db_sub["evaluate_score"];
				
				echo '<div id="table_space">';
				echo '<div id="table_information"><div id="table_title"><a href="'.$type.'/show.php?id='.h($db_sub["id"]).'">'.h($db_sub["title"]).'</a></div>';
				echo '投稿時間：'.h($db_sub["time"]).'&nbsp;'.'評価：'.h($evaluate_count).'&nbsp;';
				if($evaluate_count === 0){
					$evaluate_count = 1;
				}
				echo 'コメント：'.h($evaluate_score).'<br></div>';
				echo h($db_sub["detail_omit"]).'<br>';
			}else{
				echo '<div id="table_space">削除された記事<br>';
			}
			echo '<a href="delete_bookmark.php?type='.$type.'&id='.h((int)$article_id).'" id="bookmark">削除</a></div>';
		}else{
			echo '<div id="table_space">該当する記事が存在しません。</div>';
		}
	}
	?>
</div>

<?php
	require_once($_SERVER['DOCUMENT_ROOT']."/template/footer_tkool.php");
?>

