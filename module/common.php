<?php
	/*
		global::testモードかどうか
	*/
	$test_mode = true;
	
	/*
		書き込み用
		
		安全な形態にして描画する
	*/
	function getReviewWindowText($db,$is_need_header){
		if($is_need_header){
			$header = "/".$db["type"]."/";
		}else{
			$header = "";
		}
		$search_text_white = "";
		$c = (int)$db["evaluate_number"];
		echo '<a href="'.h($header).'show.php?id='.h($db["id"]).'" id="table_space">';
		echo '<span id="table_link">'.h($db["title"]).'</span>';
		echo h($db["detail_omit"])."<br>";
		echo '<span id="table_gray">評価：'.h((int)$db["evaluate_score"])."&nbsp;";
		echo '投稿時間：'.$db["time"].'&nbsp;'.'コメント数：'.h($c).'</span>';
		echo '</a>';
		return "";
	}
	//global変数の初期化（外部では使うな）
	$global_donotuse_referer = "";
	
	//セッションの読み込み
	//session_name("koizumi hanayo");
	if(!isset($_SESSION)){
		session_start();
	}
	
	//30回につき1回の確率で、session更新
	if(mt_rand(1,30) === 1){
		session_regenerate_id(true);
	}
	
	/*
	$key = session_name();
	print_r(empty($_REQUEST[$key]));
	if(empty($_REQUEST[$key]) || file_exists(session_save_path().DIRECTORY_SEPARATOR.'sess_'. $_REQUEST[$key])){
		session_start();
		print_r("<br>".session_name());
		print_r("<br>".session_id());
	}else{
		echo "不正データが混入しています。ブラウザを閉じてやり直してください。";
		exit;
	}
	*/
	
	//クリックジャッギング対策
	header("X-Frame-Options: DENY");
	header("Content-Type: text/html; charset=UTF-8");
	
	//各種クラスおよび関数
	class Article_Detail{
		public $title;
		public $detail;
		public $information_fromurl;
		public $time;
		public $name_show;
		public $user_id;
		public $evaluate_score;
		public $evaluate_number;
		public $img_exist;
		public $pv;
		public $usertag;
		public $totalpoint;
		public $point;
		/*
			クラスの定義時：
			なにもない場合でもコンストラクタを定義せよ
			さもないと後が怖い
		*/
		function __construct($result){
			$this->title = $result["title"];
			$this->detail = $result["detail"];
			$this->information_fromurl = $result["information_fromurl"];
			$this->time = $result["time"];
			$this->name_show = (int)$result["name_show"];
			$this->user_id = $result["user_id"];
			$this->evaluate_score = (int)$result["evaluate_score"];
			$this->evaluate_number = (int)$result["evaluate_number"];
			$this->img_exist = $result["img_exist"];
			$this->pv = (int)$result["pv"];
			$this->totalpoint = (int)$result["totalpoint"];
			$this->point = (int)$result["point"];
		}
	}
	class Search_Word{
		private $search_text_for_noroot;
		private $search_text;
		public function __construct($str){
			/*
				スペース、タブ、改行ごとに分割して、それから
			*/
			$this->search_text = "";
			$this->search_text_for_noroot = "";
			
			mb_regex_encoding("UTF-8");
			mb_internal_encoding("UTF-8");
			$str_array = mb_split("/[\s,]+/",$str);
			
			for($i=0;$i<=count($str_array)-1;$i++){
				$str_array_second = preg_split("//u",$str_array[$i],-1,PREG_SPLIT_NO_EMPTY);
				for($j=0;$j<=count($str_array_second)-2;$j++){
					$this->search_text .= $str_array_second[$j].$str_array_second[$j+1]." ";
					$this->search_text_for_noroot .= $str_array_second[$j].$str_array_second[$j+1]."蔚蔚 ";
				}
			}
		}
		function getSearchText(){
			return $this->search_text;
		}
		function getNorootSearchText(){
			return $this->search_text_for_noroot;
		}
	}
	class Search_Word_Show{
		private $search_text_for_noroot;
		private $search_text;
		public function __construct($str){
			$this->search_text = "";
			$this->search_text_for_noroot = "";
			
			mb_regex_encoding("UTF-8");
			mb_internal_encoding("UTF-8");
			$str_array = mb_split("/[\s,]+/",$str);
			
			for($i=0;$i<=count($str_array)-1;$i++){
				$str_array_second = preg_split("//u",$str_array[$i],-1,PREG_SPLIT_NO_EMPTY);
				for($j=0;$j<=count($str_array_second)-2;$j++){
					$this->search_text .= "+".$str_array_second[$j].$str_array_second[$j+1]." ";
					//rootなしレンタルサーバ対策
					$this->search_text_for_noroot .= "+".$str_array_second[$j].$str_array_second[$j+1]."蔚蔚 ";
				}
			}
		}
		public function getSearchText(){
			return $this->search_text;
		}
		public function getNorootSearchText(){
			return $this->search_text_for_noroot;
		}
	}
	
	class PDOWrapper{
		/*
			fail:PDOExceptionを吐く
		*/
		private $dbh;
		public function __construct(){
			$this->dbh = new PDO("mysql:dbname=evaltools;host=localhost;charset=utf8","root","");
			$this->dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
			$this->dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$this->dbh->setAttribute(PDO::ATTR_ORACLE_NULLS, PDO::NULL_TO_STRING);
		}
		public function commit(){
			$this->dbh->commit();
		}
		public function rollback(){
			$this->dbh->rollback();
		}
		public function beginTransaction(){
			$this->dbh->beginTransaction();
		}
		public function prepare($str){
			if(!mb_check_encoding($str,"UTF-8")){
				throw new Exception("文字コードが合わない");
			}
			return new STMTWrapper($this->dbh,$str);
		}
	}
	class STMTWrapper{
		private $stmt;
		public function __construct($dbh,$str){
			$this->stmt = $dbh->prepare($str);
		}
		public function execute($array){
			//文字コードのチェック
			for($i=0;$i<count($array);$i++){
				if(!mb_check_encoding($array[$i],"UTF-8")){
					throw new Exception("文字コードが合わない");
				}
			}
			
			//実際に処理
			$this->stmt->execute($array);
		}
		public function fetch($params){
			global $test_mode;
			if($test_mode){
				if($params !== PDO::FETCH_ASSOC){
					echo "PDO::FETCH_ASSOCでない行がある";
					throw new Exception("PDO::FETCH_ASSOCでない行がある");
				}
			}
			return $this->stmt->fetch($params);
		}
	}
	class InputMissException extends Exception{
		/*
			call_timing:
			not programming miss, however user_miss error
		*/
		public function __construct($str){
			parent::__construct($str);
		}
		public function stackTracePage(){
			if(isset($_SESSION)){
				$_SESSION["error_message"] = parent::getMessage();
			}
			header("location:error.php");
		}
	}
	function getPDO(){
		try{
			return new PDOWrapper();
		}catch(PDOException $e){
			page_fatal_error("DBerror:".$e->getMessage());
			exit;
		}
	}
	function style_check($str){
		return $str === "enterprise" || $str === "product";
	}
	function get_stylelist(){
		return ["enterprise","product"];
	}
	function getLoginInfo(){
		if(isset($_SESSION["user_id"]) && isset($_SESSION["is_login"]) && $_SESSION["is_login"] === "1"){
			echo $_SESSION["nickname"]."さん";
		}else{
			echo "あなたはログインしていません。";
		}
	}
	function draw_url($text){
		if($text === "")return "";
		$url_split = preg_split("/(\r\n|\r|\n)/",$text);
		for($i=0;$i<count($url_split);$i++){
			if(preg_match('/^(https?|ftp)(:\/\/[-_.!~*\'()a-zA-Z0-9;\/?:\@&=+\$,%#]+)$/',$url_split[$i])){
				echo '<a href="'.h($url_split[$i]).'" rel="nofollow" target="_blank">'.h($url_split[$i]).'</a><br>';
			}
		}
	}
	function check_url_info(){
		if(isset($_POST["information_fromurl"]) && mb_strlen($_POST["information_fromurl"],"UTF-8") < 1500 && mb_strlen($_POST["information_fromurl"],"UTF-8") > 0){
			//チェック
			$url_split = preg_split("/(\r\n|\r|\n)/",$_POST["information_fromurl"]);
			print_r($url_split);
			for($i=0;$i<count($url_split);$i++){
				if($url_split[$i] !== "" && !preg_match('/^(\s+)$/',$url_split[$i]) && !preg_match('/^(https?|ftp)(:\/\/[-_.!~*\'()a-zA-Z0-9;\/?:\@&=+\$,%#]+)$/',$url_split[$i])){
					throw new InputMissException("URLの形式が正しくありません");
				}
			}
			return $_POST["information_fromurl"];
		}else{
			return "";
		}
	}
	function login_check(){
		global $test_mode;
		if($test_mode){
			return true;
		}else{
			return isset($_SESSION["user_id"]) && isset($_SESSION["is_login"]) && $_SESSION["is_login"] === "1";
		}
	}
	function is_mail_style($str){
		$pattern = '/^[^0-9][a-zA-Z0-9_]+([.][a-zA-Z0-9_]+)*@\[?([\d\w\.-]+)]?$/';
    	return preg_match($pattern, $str, $matches) && checkdnsrr($matches[2], 'MX');
	}
	function getLoginID(){
		global $test_mode;
		if($test_mode)return 4;
		if(isset($_SESSION["user_id"]) && isset($_SESSION["is_login"]) && $_SESSION["is_login"] === "1" && preg_match("/^[1-9]+[0-9]*$/",$_SESSION["user_id"])){
			return (int)$_SESSION["user_id"];
		}else{
			page_fatal_error("session_broken::ログインしていないにもかかわらずIDを取得しようとしている");
		}
	}
	function page_error($status){
		if($status === 404){
			header("location:/errors/404.php");
			exit;
		}else if($status === 403){
			header("location:/errors/403.php");
			exit;
		}else if($status === 400){
			header("location:/errors/400.php");
			exit;
		}else if($status === 503){
			header("location:/errors/503.php");
			exit;
		}else if($status === 402){
			header("location:/errors/402.php");
			exit;
		}else{
			page_fatal_error("変なエラーを出そうとしているぞ！".$status);
		}
	}
	function page_fatal_error($text){
		if(isset($_SESSION)){
			$_SESSION["error_message"] = $text;
		}
		header("location:/errors/500.php");
		exit;
	}
	function get_authenticate_id(){
		$str = "";
		for($i=0;$i<=99;$i++){
			$str .= rand(0,9);//暫定
		}
		$_SESSION["authenticate_id"] = $str;
		return $str;
	}
	function check_csrf(){
		if(isset($_SESSION["authenticate_id"]) && $_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["authenticate_id"]) && strlen($_SESSION["authenticate_id"]) > 10 && 
			$_SESSION["authenticate_id"] === $_POST["authenticate_id"]){
		}else{
			throw new InputMissException("不正な処理を行いました。やり直してください。");
		}
	}
	
	function password_text_isvalid($str){
		$str_array = preg_split("//",$str,-1,PREG_SPLIT_NO_EMPTY);
		for($i=0;$i<count($str_array);$i++){
			//alphabet or 特定文字のみ許容
			$bin2 = hexdec(bin2hex($str_array[$i]));
			if(($bin2 >= 65 && $bin2 <= 90) || ($bin2 >= 97 && $bin2 <= 122) || ($bin2 >= 48 && $bin2 <= 57)){
				continue;
			}else if($bin2 === 33 || $bin2 === 63 || $bin2 === 95){
				continue;
			}else{
				return false;
			}
		}
		return true;
	}
	function h($str){
		if(!mb_check_encoding($str, "UTF-8")){
			page_error(400);
		}
		return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
	}
	function mailsend($to,$subject,$header){
		mb_language("Japanese");
		mb_internal_encoding("UTF-8");
		if(!mb_check_encoding($to, "UTF-8") || !mb_check_encoding($subject, "UTF-8") || !mb_check_encoding($header, "UTF-8")){
			page_error(400);
		}
		mb_send_mail($to,$subject,$header,"From: support@ev-things.com");
	}
	
	function will_paginate($header,$page,$page_num,$prefix,$suffix){
		/*
			引数説明：
			header:ページ文字列ヘッダ情報
			page:ページ番号(1からスタート)
			page_num:ページ最大値番号
			$prefix
			$suffix
		*/
		if($page_num < 2){
			return "";
		}
		$min = max($page-4,1);
		$max = min($page+4,$page_num);
		for($i=$min;$i<=$max;$i++){
			if($i !== $page){
				echo '<a href="'.h($header).'page='.$i.'">'.h($prefix).$i.h($suffix).'</a>';
			}else{
				echo "<b>".h($prefix).$i.h($suffix)."</b>";
			}
		}
	}
	/*
		sitemapを書き込む関数
	*/
	
	/*
		sitemapの地図（各ディレクトリにつき１つずつ、id=1,2001,・・・のときに作成する）
		修正予定：これ単体でサイトマップを書きだすようにすること＆共通化すること
	*/
	function renew_sitemap($id,$type,$dbh){
		/*
			エラーが発生するとRuntimeExceptionを返す
		*/
		$data = prep_write_sitemap_text($insert_id,$dbh,$template->type,-1);
		write_sitemap($data);
		
		//サイトマップの編成(idが2000とかそれくらいの数字の時の処理) ただし、テスト目的でtrueにしておく
		if(true || $insert_id % 2000 === 1){
			write_sitemap_structure($insert_id,$template->type);
		}
	}
	function prep_write_sitemap_text($id,$dbh,$type,$exception){
		$set_id = (int)($id / 2000);
		$stmt = $dbh->prepare("SELECT id FROM ".$type."_review WHERE id BETWEEN ? AND ?");
		$stmt->execute(array($set_id*2000+1,2000*($set_id+1)));
		
		$data = array("text" => "", "id" => (int)($id/2000));
		$data["text"] = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\" xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\">\n";
		while($db = $stmt->fetch(PDO::FETCH_ASSOC)){
			if($exception !== (int)$db["id"]){
				$data["text"] .= "<url>\n	<loc>";
				$data["text"] .= "http://www.ev-things.com/".$type."/show.php=id=".(int)($db["id"]);
				$data["text"] .= "</loc>\n	<priority>1.0</priority>\n	<changefreq>daily</changefreq>\n</url>\n";
			}
		}
		$data["text"] .= "</urlset>";
		return $data;
	}
	function write_sitemap($data){
		if(preg_match("/^[0-9]+$/",(string)$data["id"])){
			//ファイルのチェック(sitemapは2000個ごとに、50000まで持てるので100,000,000reviewまで)
			$waittable_time = 0;
			
			//あらかじめ文章を準備する
			
			//書き込み処理
			while(true){
				if(!file_exists("sitemap/sitemap".$data["id"].".xml") || is_writable("sitemap/sitemap".$data["id"].".xml")){
					$fe = !file_exists("sitemap/sitemap".$data["id"].".xml");
					//実際にファイルを書く
					$handle = fopen("sitemap/sitemap".$data["id"]."_temp.xml","w");
					fwrite($handle,$data["text"]);
					fclose($handle);
					
					//処理終了
					if(!rename("sitemap/sitemap".$data["id"]."_temp.xml","sitemap/sitemap".$data["id"].".xml")){
						throw new Exception("file cannot rewrite");
					}
					break;
				}else{
					sleep(10);
					$waittable_time ++;
					if($waittable_time === 100){
						throw new Exception("待ち時間が限界を超えました");
					}
				}
			}
		}else{
			throw new Exception("NumberFormatException in write_sitemap");
		}
	}
	function write_sitemap_structure(){
		//共通パート前半
		$str = '<?xml version="1.0" encoding="UTF-8"?>';
		$str .= '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
		
		//固有パートの記述
		$dbh = getPDO();
		$style_list = get_stylelist();
		for($a=0;$a<count($style_list);$a++){
			$stmt = $dbh->prepare("SELECT MAX(id) FROM ?;");
			$stmt->execute(array($style_list[$a]."_review"));
			if($data = $stmt->fetch(PDO::FETCH_ASSOC)){
				$datanum = max(1,(int)(($data["MAX(id)"]-1)/2000)+1);
				for($i=0;$i<$datanum;$i++){
					$str .= "<sitemap>";
					$str .= "<loc>http://ev-things.com/".$style_list[$a]."/sitemap/sitemap".$i.".xml</loc>";
					$str .= "</sitemap>";
				}
			}
		}
		//共通パート後半
		$str .= '</sitemapindex>';
		
		while(true){
			if(!file_exists("sitemap/sitemap_index.xml") || is_writable("sitemap/sitemap_index.xml")){
				$fe = !file_exists("sitemap/sitemap_index_tmp.xml");
				//実際にファイルを書く
				$handle = fopen("sitemap/sitemap_index_tmp.xml","w");
				fwrite($handle,$str);
				fclose($handle);
				
				//処理終了
				if(!rename("sitemap/sitemap_index_tmp.xml","sitemap/sitemap_index.xml")){
					throw new Exception("file cannot rewrite");
				}
				break;
			}else{
				sleep(10);
				$waittable_time ++;
				if($waittable_time === 100){
					throw new Exception("待ち時間が限界を超えました");
				}
			}
		}
	}
	
	function check_review_text(){
		if(isset($_POST["review_text"]) && mb_strlen($_POST["review_text"],"UTF-8") >= 20 && mb_strlen($_POST["review_text"],"UTF-8") <= 2500){
			return $_POST["review_text"];
		}else{
			throw new InputMissException("レビューの本文は20～2500文字までです");
		}
	}
	
	function check_tag(){
		//投稿ユーザー用のタグ情報（５つまで投稿許可）
		$return_arr = ["","","","",""];
		$count = 0;
		for($i=0;$i<=4;$i++){
			if(isset($_POST["tag".$i]) && mb_strlen($_POST["tag".$i],"UTF-8") >= 0 && mb_strlen($_POST["tag".$i],"UTF-8") < 50){
				if($_POST["tag".$i] !== ""){
					$return_arr[$count] = $_POST["tag".$i];
					$count ++;
				}
			}else{
				throw new InputMissException("値の不正の検知");
			}
		}
		return $return_arr;
	}
	
	function check_evaluate(){
		if(isset($_POST["evaluate"]) && preg_match("/[0-9]+/",$_POST["evaluate"]) && (int)$_POST["evaluate"] <= 100){
			return $_POST["evaluate"];
		}else{
			throw new InputMissException("評価点は0～100までの数値です");
		}
	}
	function check_nameshow(){
		if(isset($_POST["name_show"]) && ($_POST["name_show"] === "0" || $_POST["name_show"] === "1")){
			return $_POST["name_show"];
		}else{
			throw new InputMissException("名前掲載の可否は1か0の形式にしてください。");
		}
	}
	function check_title(){
		if(isset($_POST["title"]) && mb_strlen($_POST["title"],"UTF-8") >= 4 && mb_strlen($_POST["title"],"UTF-8") <= 50){
			return $_POST["title"];
		}else{
			throw new InputMissException("タイトルの長さは4～50文字です。");
		}
	}
	
	/*
		file_style
	*/
	function file_check($file_form_name){
		//未定義であるなどのケースでは、例外を出す
		if(!isset($_FILES[$file_form_name]["error"]) || !is_int($_FILES[$file_form_name]["error"])){
			throw new RuntimeException("パラメータの不正");
		}
		
		$f = $_FILES[$file_form_name]["error"];
		switch ($f) {
			case UPLOAD_ERR_OK:
				break;
			case UPLOAD_ERR_NO_FILE:
				return false;
			case UPLOAD_ERR_INI_SIZE:  // php.ini定義の最大サイズ超過
			case UPLOAD_ERR_FORM_SIZE: // フォーム定義の最大サイズ超過
				throw new RuntimeException('ファイルサイズが大きすぎます');
			default:
				throw new RuntimeException('その他のエラーが発生しました');
		}
		if($_FILES[$file_form_name]["size"] > 1024 * 1024){
			throw new RuntimeException("ファイルのサイズが大きすぎます。");
		}
		
		$finfo = new finfo(FILEINFO_MIME_TYPE);
		$ext = array_search(
			$finfo->file($_FILES[$file_form_name]["tmp_name"]),
			array(
				"gif" => "image/gif",
				"jpg" => "image/jpeg",
				"png" => "image/png"
			),
			true
		);
		if(!$ext){
			throw new RuntimeException("ファイル形式の不正。アップロードできるファイルはjpg,png,gifファイルです。");
		}
		
		$f2 = $_FILES[$file_form_name]["name"];
		if(!file_style_check($f2)){
			throw new RuntimeException("ファイル名に使用できない文字列が含まれています。");
		}
		
		return array("extension" => $ext,"name" => $_FILES[$file_form_name]["tmp_name"]);
	}
	function file_style_check($str){
		//使ってはならないファイル名を検知
		if(strpos($str,"\0") || strpos($str,"/") || strpos($str,"*") || 
			strpos($str,":") || strpos($str,"<") || strpos($str,">") || strpos($str,';') ||
			strpos($str,'"') || strpos($str,'|') || strpos($str,'?') || strpos($str,'\\')){
			throw new InputMissException("ファイル名の不正が発覚しました。");
		}
		return true;
	}
?>