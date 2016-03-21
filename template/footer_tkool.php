</div>

		</div>
		<div id="rightpart">
			
		</div>
		
		
	</div>
	
	<!--when not logined?-->
	<?php
		if(!login_check()){
			?>
				<div id="login_only_func" hidden>
					<div id="login_only_space">
						ログインすることで選択した機能が利用できます。ユーザー登録した場合
						投稿や評価などの機能が利用できます。
						
						<a id="login_only_button" href="/signin.php">ユーザー登録</a>
						<a id="login_only_button" href="login.php">ログイン</a>
						<span id="login_only_button" onClick="window_close();">閉じる</span>
					</div>
				</div>
				<div id="error_func" hidden>
					<div id="error_only_space">
						<span id="error_text"></span>
						<span id="login_only_button" onClick="window_close();">閉じる</span>
					</div>
				</div>
			<?php
		}
		
	?>
	<div id="error_func" hidden>
		<div id="login_only_space">
			不正な処理を行いました。次の要因が考えられます。
			<li>インターネットに接続されていない</li>
			この場合、インターネットに接続されているかを確認してやり直してください。
			<li>ソース画面を閲覧した場合は更新してやり直してください。</li>
			<span id="login_only_button" onClick="window_error_func_close();">閉じる</span>
		</div>
	</div>
	<div id="footer">
		<div id="copyright">
			<br>
			©2015 log all rights reserved.
			<br><br>
		</div>
	</div>
	<div id="adspace_moblie">
		<!--ここに記述しても記述した内容は表示されません。-->
	</div>
</div>
</body>
</html>