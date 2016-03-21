<div id="box_1">
	<div id="box_title">E2BC企業</div>
	<?php
		if(!login_check()){
			echo '<a id="odd" href="show.php">レビューを読む</a>';
			echo '<a id="even" onClick="window_show_login()">レビューを書く</a>';
			echo '<a id="odd" href="/signin.php">ユーザー登録</a>';
			
		}else{
			echo '<a id="odd" href="/user.php?id='.getLoginID().'">マイページ</a>';
			echo '<a id="even" href="review_create.php">レビューを書く</a>';
			echo '<a id="odd" href="show.php">レビューを読む</a>';
		}
	?>
</div>