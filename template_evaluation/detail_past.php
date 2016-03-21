<?php
	function show_ownpart(){
		global $article;
		//appstoreのレビュー
		echo '<a href="http://www.apple.com/jp/search/'.h($article->title).'?src=globalnav"><img src="img_not_userload/apple.png"/></a>';
		
		//google playからダウンロード
		echo '<a href="http://'.h($article->title).'"><img src="img_not_userload/googleplay.png"/></a>';
	}
?>