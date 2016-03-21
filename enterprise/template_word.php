<?php
	class Template_Word{
		public $title;
		public $goodvalue;
		public $randvalue;
		public $type;
		public $meta_description;
		public $meta_keyword;
		public function __construct(){
			$this->title = "企業";
			$this->goodvalue = "優良評価企業";
			$this->randvalue = "ランダム企業";
			$this->meta_description = "就活生にも転職者必見！ の企業レビュー情報を掲載しています。企業の働き方、福利厚生、そしてブラック企業か優良企業かの事情を取り扱っています。";
			$this->meta_keyword = " 企業 福利厚生 レビュー 評価 ブラック企業 優良企業";
			$this->type = "enterprise";
		}
	}
	$template = new Template_Word();
	
?>