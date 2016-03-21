<?php
	require_once($_SERVER['DOCUMENT_ROOT']."/safe/securimage.php");
	require_once($_SERVER['DOCUMENT_ROOT']."/module/common.php");
	$error_message = "";
	
	if(!login_check()){
		header("location:login.php");
		exit;
	}
	echo $_POST["article_id"];
	if($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["comment"]) && isset($_POST["article_id"]) && isset($_POST["title"])){
		try{
			//必要なデータの取得
			if(!preg_match("/^[1-9][0-9]*$/",$_POST["article_id"])){
				throw new InputMissException("記事番号が不正です。");
			}
			if(mb_strlen($_POST["comment"],"UTF-8") > 2500 || mb_strlen($_POST["comment"],"UTF-8") < 20){
				throw new InputMissException("文字数は20-2500の範囲内に収めてください。");
			}
			if(mb_strlen($_POST["title"],"UTF-8") > 50 || mb_strlen($_POST["title"],"UTF-8") < 4){
				throw new InputMissException("タイトルは4文字～50文字の範囲内に収めてください。");
			}
			//画像認証の値チェック
			$image = new Securimage();
			if($image->check($_POST["captcha_code"]) !== true) {
				throw new InputMissException("画像認証の文字列が正しくありません");
			}
			
			$dbh = getPDO();
			
			$user_id = getLoginID();
			$article_id = (int)$_POST["article_id"];
			$comment = $_POST["comment"];
			$title = $_POST["title"];
			
			//CSRFのチェック
			check_csrf();
			
			//記事のユーザーの取得
			$stmt = $dbh->prepare("SELECT user_id FROM ".$template->type."_review WHERE id = ?;");
			$stmt->execute(array($article_id));
			if($data = $stmt->fetch(PDO::FETCH_ASSOC)){
				$article_user_id = (int)$data["user_id"];
			}else{
				throw new InputMissException("存在しないデータへのコメント");
			}
			
			//同一のニュースがあるかチェック
			$same_news_exist = false;
			$stmt = $dbh->prepare("SELECT COUNT(id) FROM news WHERE user_id = ? AND article_id = ? AND type = ?;");
			$stmt->execute(array($article_user_id,$article_id,$template->type));
			if($data = $stmt->fetch(PDO::FETCH_ASSOC)){
				$same_news_exist = (int)$data["COUNT(id)"] !== 0;
			}else{
				throw new InputMissException("ニュース記事が数えられません！");
			}
			
			$dbh->beginTransaction();
			try{
				$stmt = $dbh->prepare("INSERT INTO ".$template->type."_comment (user_id,article_id,text,title) VALUES (?,?,?,?)");
				$stmt->execute(array($user_id,$article_id,$comment,$title));
				
				//自分で投稿していたか確かめ、そうでなければ通知リストに加える
				if($article_user_id !== $user_id){
					$stmt = $dbh->prepare("INSERT INTO news (user_id,article_id,title,type) VALUES (?,?,?,?);");
					$stmt->execute(array($user_id,$article_id,"記事にコメントが付きました",$template->type));
				}
				$dbh->commit();
				header("location:show.php?id=".$article_id);
			}catch(Exception $e_2){
				$dbh->rollback();
				throw new Exception($e_2->message());
			}
		}catch(InputMissException $e){
			$e->stackTracePage();
		}catch(Exception $e){
			page_fatal_error("comment_send/78/".$e->getMessage());
		}
	}else{
		//page_error(400);
	}
?>
