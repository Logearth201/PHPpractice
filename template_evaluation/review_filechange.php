<?php
	require_once($_SERVER['DOCUMENT_ROOT']."/module/common.php");
	require_once($_SERVER['DOCUMENT_ROOT']."/module/review_send.php");
	try{
		//ログインチェック
		if(!login_check()){
			header("location:login.php");
			exit;
		}
		//idの取得
		$user_id = getLoginID();
		$id = (int)$_GET["id"];
		if($id <= 0){
			throw new InputMissException("該当するidはありません。");
		}
		
		//ユーザーの存在性
		$dbh = getPDO();
		$stmt = $dbh->prepare("SELECT COUNT(id) FROM ".$template->type."_review WHERE id = ? AND user_id = ?;");
		$stmt->execute(array($id,$user_id));
		if($data = $stmt->fetch(PDO::FETCH_ASSOC)){
			if((int)$data["COUNT(id)"] !== 1){
				page_error(403);
			}
		}
		
		//画像の差し替え
		if($_SERVER["REQUEST_METHOD"] === "POST"){
			check_csrf();
			//デリートの取得
			if(!isset($_POST["vanish"])){
				throw new InputMissException("vanishメソッドのセットし忘れ");
			}
			$delete = $_POST["vanish"] === "1" ? true : false;
			
			if($delete){
				//png,jpg,gifの削除
				if(file_exists("img/".$id."image.jpg"))unlink("img/".$id."image.jpg");
				if(file_exists("img/".$id."image.png"))unlink("img/".$id."image.png");
				if(file_exists("img/".$id."image.gif"))unlink("img/".$id."image.gif");
				
				//SQLのセット
				$stmt = $dbh->prepare("UPDATE ".$template->type."_review SET img_exist = '' WHERE id = ?");
				$stmt->execute(array((int)$id));
				header("location:show.php?id=".$id);
			}else{
				$fileinfo = null;
				try{
					$fileinfo = file_check("imgfile_data");
					if($fileinfo){
						//png,jpg,gifの削除
						if(file_exists("img/".$id."image.jpg"))unlink("img/".$id."image.jpg");
						if(file_exists("img/".$id."image.png"))unlink("img/".$id."image.png");
						if(file_exists("img/".$id."image.gif"))unlink("img/".$id."image.gif");
						
						//ファイルのアップロード
						if(move_uploaded_file($_FILES["imgfile_data"]["tmp_name"],"img/".$id."image.".$fileinfo["extension"]) ){
							chmod($template->type."/img/".$id."image.".$fileinfo["extension"],0644);
						}else{
							throw new InputMissException("ファイルのアップロードに失敗しました。");
						}
						$stmt = $dbh->prepare("UPDATE ".$template->type."_review SET img_exist = ? WHERE id = ?");
						$stmt->execute(array($fileinfo["extension"],(int)$id));
					}
					header("location:show.php?id=".$id);
				}catch(RuntimeException $e){
					echo $e->getMessage();
					$error_message = "ファイルのアップロードに失敗しました。拡張子を確かめの上でやり直してください。";
				}catch(PDOException $e){
					page_fatal_error("review_filechange//".$e->getMessage());
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
	$title = "ファイルの差し替え";
	require_once($_SERVER['DOCUMENT_ROOT']."/template/header_tkool.php");
?>
<div id="content">
	<h1>ファイルの入れ替え</h1>
	<form method="post" enctype="multipart/form-data">
		画像の挿入：(jpg,gif,png 1MBまで)<br>
		<input type="file" name="imgfile_data"></input><br>
		※画像を削除する場合にのみ「画像を削除する」にチェックをつけてください。<br>
		<input type="radio" name="vanish" value="1">画像を削除する</input><br>
		<input type="radio" name="vanish" value="0" checked>画像を削除しない</input><br>
		<input type="hidden" name="authenticate_id" value="<?php echo h(get_authenticate_id()); ?>"></input>
		<input type="submit" value="送信"></input>
	</form>
</div>
<?php
	require_once($_SERVER['DOCUMENT_ROOT']."/template/footer_tkool.php");
?>