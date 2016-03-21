<?php
	//user_idの取得
	require_once($_SERVER['DOCUMENT_ROOT']."/module/common.php");
	$logined = login_check();
	if(!$logined){
		echo "NG";
		exit;
	}
	$user_id = getLoginID();
	
	//評価ツール
	
	if(!isset($_GET["id"]) || !preg_match("/^[0-9]+[1-9]*$/",$_GET["id"])){
		echo "エラーが発生しました:idパラメータの不正";
		exit;
	}
	$id = $_GET["id"];
	$level = $_GET["level"];
	
	
	if($level !== "parent" && $level !== "child"){
		echo "ERROR:level=parent or child! Or refused!";
		exit;
	}else if($level === "parent"){
		$style = "review";
	}else if($level === "child"){
		$style = "comment";
	}
	
	
	if($_SERVER["REQUEST_METHOD"] === "POST"){
		try{
			check_csrf();
			$dbh = getPDO();
			
			$stmt = $dbh->prepare("SELECT eval_point FROM ".$template->type."_log_eval_".$level." WHERE article_id = ? AND user_id = ?;");
			$stmt->execute(array($id,$user_id));
			
			$query = [];
			$eval = $_POST["eval"] === "0" ? "0" : "1";
			$result = $stmt->fetch(PDO::FETCH_ASSOC);
			if($result){
				$getv = $result["eval_point"];
				if($getv === $eval){
					echo "none";
					exit;
				}
				$stmt = $dbh->prepare("UPDATE ".$template->type."_log_eval_".$level." SET eval_point = ? WHERE article_id = ? AND user_id = ?;");
				$stmt->execute(array((int)$eval,$id,$user_id));
			}else{
				$stmt = $dbh->prepare("INSERT INTO ".$template->type."_log_eval_".$level." (user_id,eval_point,article_id) VALUES (?, ?, ?);");
				$stmt->execute(array($user_id,(int)$eval,$id));
			}
			
			//評価値の強制セット(エラーが出た場合は次に保留)
			$dbh->beginTransaction();
			try{
				$stmt = $dbh->prepare("SELECT COUNT(id) FROM ".$template->type."_log_eval_".$level." WHERE article_id = ?;");
				$stmt->execute(array($id));
				$result = $stmt->fetch(PDO::FETCH_ASSOC);
				if(!$result){
					throw new Exception("A");
				}
				$totalpoint = (int)$result["COUNT(id)"];
				
				$stmt = $dbh->prepare("SELECT COUNT(id) FROM ".$template->type."_log_eval_".$level." WHERE article_id = ? AND eval_point = 1;");
				$stmt->execute(array($id));
				$result = $stmt->fetch(PDO::FETCH_ASSOC);
				if(!$result){
					throw new Exception("A");
				}
				$point = (int)$result["COUNT(id)"];
				
				//スコア反映
				$stmt = $dbh->prepare("UPDATE ".$template->type."_".$style." SET totalpoint = ? , point = ? WHERE id = ?;");
				$stmt->execute(array($totalpoint,$point,$id));
				$dbh->commit();
				
				/*
					返信ヘッダ：id,parent or child,good,bad,your_evaluate
				*/
				echo h($id."#".$level."#".$point."#".($totalpoint-$point)."#".$eval);
			}catch(Exception $e){
				$dbh->rollback();
				throw new Exception($e->getMessage());
			}
		}catch(InputMissException $e){
			echo "error:入力miss";
		}catch(Exception $e){
			page_fatal_error("getuser_eval/90/".$e->getMessage());
		}
	}
?>