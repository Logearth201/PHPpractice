<?php
	/*
		警告：
		管理者は、必ずこれを実行した後削除してください。
	*/
	require_once("module/common.php");
	
	$mysqli = getMySqliObject();//game用、コメント用に分割する
	
	//tableデータを挿入する(index:maintag,levelで)
	$query = <<<HOGE
CREATE TABLE userdata (
	id int NOT NULL AUTO_INCREMENT,
	login_misstime int NOT NULL DEFAULT 0,
	login_miss_timestamp text COLLATE utf8_bin NOT NULL,
	password_forget_key text COLLATE utf8_bin NOT NULL,
	password_forget_time int NOT NULL DEFAULT 0,
	nickname text COLLATE utf8_bin NOT NULL,
	self_introduce text COLLATE utf8_bin NOT NULL,
	mail text COLLATE utf8_bin,
	admin TINYINT NOT NULL DEFAULT 0,
	password text COLLATE utf8_bin NOT NULL,
	existance int NOT NULL DEFAULT 1,
	trustworthy int NOT NULL DEFAULT 100,
	time timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
	sex text NOT NULL,
	year int NOT NULL,
	website text NOT NULL,
	PRIMARY KEY(id)
);
CREATE TABLE temp_user(
	id int AUTO_INCREMENT,
	mail text COLLATE utf8_bin NOT NULL,
	date int NOT NULL,
	authenticate_key text COLLATE utf8_bin NOT NULL,
	PRIMARY KEY(id)
);
CREATE TABLE enterprise_review (
	id int AUTO_INCREMENT,
	user_id int NOT NULL,
	title text NOT NULL COLLATE utf8_bin,
	tag_playerset_num int NOT NULL DEFAULT 0,
	detail text NOT NULL COLLATE utf8_bin,
	detail_omit text NOT NULL COLLATE utf8_bin,
	information_fromurl text NOT NULL COLLATE utf8_bin,
	evaluate_score int NOT NULL DEFAULT 50,
	evaluate_number int NOT NULL DEFAULT 1,
	totalpoint int DEFAULT 0,
	point int DEFAULT 0,
	pv int DEFAULT 0,
	name_show tinyint NOT NULL DEFAULT 1,
	img_exist text NOT NULL,
	time timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
	search_word text NOT NULL COLLATE utf8_bin,
	search_word_noroot text NOT NULL COLLATE utf8_bin,
	type text NOT NULL COLLATE utf8_bin,
    PRIMARY KEY(id)
);
ALTER TABLE enterprise_review ADD INDEX(`user_id`);
ALTER TABLE enterprise_review ADD FOREIGN KEY (`user_id`) REFERENCES `userdata`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;
alter table enterprise_review add fulltext(detail);
alter table enterprise_review add fulltext(title);

CREATE TABLE enterprise_comment (
	id int NOT NULL AUTO_INCREMENT,
	user_id int NOT NULL,
	article_id int NOT NULL,
	text text NOT NULL COLLATE utf8_bin,
	evaluate int NOT NULL DEFAULT 50,
	totalpoint int DEFAULT 0,
	point int DEFAULT 0,
	time timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
	type text NOT NULL COLLATE utf8_bin,
	PRIMARY KEY(id)
);
ALTER TABLE enterprise_comment ADD INDEX(article_id);
ALTER TABLE enterprise_comment ADD FOREIGN KEY (`article_id`) REFERENCES `enterprise_review`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE enterprise_comment ADD FOREIGN KEY (`user_id`) REFERENCES `userdata`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;

CREATE TABLE enterprise_log_eval_parent (
	id int NOT NULL AUTO_INCREMENT,
	user_id int NOT NULL,
	article_id int NOT NULL,
	eval_point int NOT NULL,
	time timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY(id)
);
ALTER TABLE enterprise_log_eval_parent ADD INDEX(user_id);
ALTER TABLE enterprise_log_eval_parent ADD FOREIGN KEY (`article_id`) REFERENCES `enterprise_review`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;

CREATE TABLE enterprise_log_eval_child (
	id int NOT NULL AUTO_INCREMENT,
	user_id int NOT NULL,
	article_id int NOT NULL,
	eval_point int NOT NULL,
	time timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY(id)
);
ALTER TABLE enterprise_log_eval_child ADD INDEX(user_id);
ALTER TABLE enterprise_log_eval_child ADD FOREIGN KEY (`article_id`) REFERENCES `enterprise_comment`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;

CREATE TABLE news(
	id int NOT NULL AUTO_INCREMENT,
	title text NOT NULL COLLATE utf8_bin,
	user_id int NOT NULL,
	article_id int NOT NULL,
	type text NOT NULL COLLATE utf8_bin,
	already_read tinyint NOT NULL DEFAULT 0,
	PRIMARY KEY(id)
);
ALTER TABLE news ADD INDEX(`user_id`);
ALTER TABLE news ADD INDEX(`already_read`);

CREATE TABLE violation_table(
	id int NOT NULL AUTO_INCREMENT,
	type text NOT NULL COLLATE utf8_bin,
	violation_id int NOT NULL,
	text text NOT NULL COLLATE utf8_bin,
	PRIMARY KEY(id)
);

CREATE TABLE bookmark(
    id int AUTO_INCREMENT,
    user_id int NOT NULL,
    type text NOT NULL,
    booked_id int NOT NULL,
    PRIMARY KEY(id)
);
ALTER TABLE bookmark ADD INDEX(user_id);
ALTER TABLE bookmark ADD FOREIGN KEY (`user_id`) REFERENCES `userdata`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;

CREATE TABLE product_review (
	id int AUTO_INCREMENT,
	user_id int NOT NULL,
	title text NOT NULL COLLATE utf8_bin,
	tag_playerset_num int NOT NULL DEFAULT 0,
	detail text NOT NULL COLLATE utf8_bin,
	detail_omit text NOT NULL COLLATE utf8_bin,
	information_fromurl text NOT NULL COLLATE utf8_bin,
	evaluate_score int NOT NULL DEFAULT 50,
	evaluate_number int NOT NULL DEFAULT 1,
	totalpoint int DEFAULT 0,
	point int DEFAULT 0,
	pv int DEFAULT 0,
	name_show tinyint NOT NULL DEFAULT 1,
	img_exist text NOT NULL,
	time timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
	search_word text NOT NULL COLLATE utf8_bin,
	search_word_noroot text NOT NULL COLLATE utf8_bin,
	type text NOT NULL COLLATE utf8_bin,
    PRIMARY KEY(id)
);
ALTER TABLE product_review ADD INDEX(`user_id`);
ALTER TABLE product_review ADD FOREIGN KEY (`user_id`) REFERENCES `userdata`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;
alter table product_review add fulltext(detail);
alter table product_review add fulltext(title);

CREATE TABLE product_comment (
	id int NOT NULL AUTO_INCREMENT,
	user_id int NOT NULL,
	article_id int NOT NULL,
	text text NOT NULL COLLATE utf8_bin,
	evaluate int NOT NULL DEFAULT 50,
	totalpoint int DEFAULT 0,
	point int DEFAULT 0,
	time timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
	type text NOT NULL COLLATE utf8_bin,
	PRIMARY KEY(id)
);
ALTER TABLE product_comment ADD INDEX(article_id);
ALTER TABLE product_comment ADD FOREIGN KEY (`article_id`) REFERENCES `product_review`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE product_comment ADD FOREIGN KEY (`user_id`) REFERENCES `userdata`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;

CREATE TABLE product_log_eval_parent (
	id int NOT NULL AUTO_INCREMENT,
	user_id int NOT NULL,
	article_id int NOT NULL,
	eval_point int NOT NULL,
	time timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY(id)
);
ALTER TABLE product_log_eval_parent ADD INDEX(user_id);
ALTER TABLE product_log_eval_parent ADD FOREIGN KEY (`article_id`) REFERENCES `product_review`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;

CREATE TABLE product_log_eval_child (
	id int NOT NULL AUTO_INCREMENT,
	user_id int NOT NULL,
	article_id int NOT NULL,
	eval_point int NOT NULL,
	time timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY(id)
);
ALTER TABLE product_log_eval_child ADD INDEX(user_id);
ALTER TABLE product_log_eval_child ADD FOREIGN KEY (`article_id`) REFERENCES `product_comment`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;

HOGE;
	send_query_safe($mysqli,$query);
	
	//インデックス
	$query = [];
	$query[0] = "";
	
	//トランザンクションの開始
	$mysqli->query("set autocommit = 0;");
	$mysqli->query("BEGIN;");
	
	//シード(seed)の挿入
	$query = [];
	$query[0] = 'INSERT INTO userdata (nickname, mail, admin, password) VALUES ("不正行為が発覚しました", "fusei.love@gmail.com", 0, "'.password_hash("caewrncu38r92rva7186fnaycoi32r2664zr4mtvp9r246czf246481wc686zrw44186br1z8v144f86w1c6",PASSWORD_DEFAULT).'")';
	$query[1] = 'INSERT INTO userdata (nickname, mail, admin, password) VALUES ("運営", "madoka.love@gmail.com", 1, "'.password_hash("test",PASSWORD_DEFAULT).'")';
	send_query_safe($mysqli,$query);
	
	//コミットする
	$mysqli->query("COMMIT;");
	
	//成功したらindexに戻る
	echo "インストールに成功しました。";
?>