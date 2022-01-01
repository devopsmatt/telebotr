<?php

include __DIR__.'/../common.inc.php';

$result = doQuery("SELECT ID FROM Sources;");
if($result) {
    while($row = $result->fetch(PDO::FETCH_ASSOC)) {
	$source_id = $row["ID"];

// ====================================================================================
// Computa le statistiche per sorgente per post per clicks
// ====================================================================================

        $result2 = doQuery("SELECT SUM(Views) AS Views, COUNT(ID) AS Posts, DATE_SUB(CURDATE(), INTERVAL 1 DAY) AS DayDate FROM Posts WHERE sourceId=:source_id AND DATE(publishDate)=DATE_SUB(CURDATE(), INTERVAL 1 DAY);", array(":source_id" => $source_id));
	if($result2) {
	    $row2 = $result->fetch(PDO::FETCH_ASSOC);
	
	    $source_views = intval($row2["Views"]);
	    $source_posts = intval($row2["Posts"]);
	    $source_day = $row2["DayDate"];

	    doQuery("INSERT INTO SourcesStats(Day,sourceId,numPosts,numClicks) VALUES (:source_day,:source_id,:source_posts,:source_views);", array(":source_day" => $source_day,":source_id" => $source_id,":source_posts" => $source_posts,":source_views" => $source_views));
	}

// ====================================================================================
// Pulisci vecchi post
// ====================================================================================
	$tmp_source = new Source($source_id);
	if($tmp_source->getAVP("daysRetention")) {
	    $post_retention = $tmp_source->getAVP("daysRetention");
	} else {
	    // Default: 180 days
	    $post_retention = 180;
	}
	doQuery("DELETE FROM Posts WHERE TIMESTAMPDIFF(DAY,addDate,now()) > :posts_retention",array(":post_retention" => $post_retention));
    }
}
