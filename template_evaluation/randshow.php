<?php
	if(!isset($dbh)){
		$dbh = getPDO();
	}
	$search_text_rnd = "";
	$stmt = $dbh->prepare("SELECT * FROM ".$template->type."_review ORDER BY RAND() LIMIT 0, 3");
	$stmt->execute(array());
	while($db = $stmt->fetch(PDO::FETCH_ASSOC)){
		$search_text_rnd .= getReviewWindowText($db,false);
	}
	
	$search_text_white = "";
	$stmt = $dbh->prepare("SELECT * FROM ".$template->type."_review ORDER BY RAND() LIMIT 0, 3");
	$stmt->execute(array());
	while($db = $stmt->fetch(PDO::FETCH_ASSOC)){
		$search_text_white .= getReviewWindowText($db,false);
	}
?>
