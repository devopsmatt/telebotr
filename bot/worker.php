<?php

require_once __DIR__.'/TeleBot.php';

$log->debug("Bot worker START");

$result = doQuery("SELECT ID,botToken,TIMESTAMPDIFF(MINUTE, lastPublish, NOW()) AS lastPublish, errorCounter, lastError FROM Bots WHERE isEnable=1;");
if($result) {
    while($row = $result->fetch(PDO::FETCH_ASSOC)) {
	$bot_token = $row["botToken"];
	$bot_id = $row["ID"];

	$log->debug("Bot $bot_id is running...");

	try {
	    $bot = new Bot($bot_id);
	    $last_publish = $row["lastPublish"]; /* How minutes since last publish ? */
	    $error_counter = $row["errorCounter"]; /* How many consequential errors ? */

	    if($error_counter > 10) {
	        doQuery("UPDATE Bots SET isEnable=0 WHERE ID=$bot_id;");
		$last_error = stripslashes($row["lastError"]);
		$log->error("BOT $bot_id DISABLED due multiple errors: $last_error");
	    } else {
		if(empty($last_publish)||($last_publish > 30)) {
		    $bot = new TeleBot($bot_id, $bot_token, 'TeleBotChat');
		    $bot->runPoll();
		}
	    }
	} catch (Exception $e) {
	    $log->error("Bot $bot_id got an exception: $e");
	    // Don't stop if one bot raise an exception
	}
    }
}

$log->debug("Bot worker END");
