<?php
	function getMySqliObject(){
		$mysqli = new mysqli("localhost","root","","webmake");
		if($mysqli->connect_error){
			echo "暗黒大魔境";
			exit;
		}else{
			$mysqli->set_charset("utf8");
		}
		return $mysqli;
	}
	
	$mysqli = getMySqliObject();
	
	//query(init_mode)
	$mysqli->query("BEGIN;");
	$query = [];
	$query[0] = 'CREATE TABLE userdata (id int NOT NULL AUTO_INCREMENT, login_id text COLLATE utf8_bin, mail text COLLATE utf8_bin,admin TINYINT NOT NULL DEFAULT 0,password text COLLATE utf8_bin,PRIMARY KEY(id))';
	$query[1] = 'CREATE TABLE quizdata (id int NOT NULL AUTO_INCREMENT, user_id int NOT NULL DEFAULT 2, explain_game text NOT NULL COLLATE utf8_bin, title text NOT NULL COLLATE utf8_bin, advertise text NOT NULL COLLATE utf8_bin, maindata text NOT NULL COLLATE utf8_bin, rightdata text NOT NULL COLLATE utf8_bin,PRIMARY KEY(id))';
	$query[2] = 'CREATE TABLE noveldata (id int NOT NULL AUTO_INCREMENT, user_id int NOT NULL DEFAULT 2, explain_game text NOT NULL COLLATE utf8_bin, title text NOT NULL COLLATE utf8_bin, advertise text NOT NULL COLLATE utf8_bin, maindata text NOT NULL COLLATE utf8_bin, rightdata text NOT NULL COLLATE utf8_bin,PRIMARY KEY(id))';
	$query[3] = 'CREATE TABLE game_comment (text TEXT COLLATE utf8_bin NOT NULL, name TEXT COLLATE utf8_bin NOT NULL DEFAULT "", mail TEXT COLLATE utf8_bin NOT NULL, password text COLLATE utf8_bin NOT NULL,time TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, id int AUTO_INCREMENT, PRIMARY KEY(id))';
	
	for($i=0;$i<=2;$i++){
		$result = $mysqli->query($query[$i]);
		if($result !== true){
			$mysqli->query("ROLLBACK;");
			echo "エラーが発生しました";
			exit;
		}
	}
	
	$mysqli->query("COMMIT;");
?>