<?php

/******************************************************************************************************

    TeleBotr -  Telegram publishing platform

    Copyright (C) 2021  Michele <o-zone@zerozone.it> Pinassi

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <https://www.gnu.org/licenses/>.

******************************************************************************************************/

include __DIR__.'/common.inc.php';

require_once __DIR__.'/bot/TeleBot.php';

// ====================================================================================
// RSS Feeder
// ====================================================================================

$result = doQuery("SELECT ID FROM Sources WHERE isEnable=:enable;", array(":enable" => 1));
if($result) {
    while($row = $result->fetch(PDO::FETCH_ASSOC)) {
	$source_id = $row["ID"];
	RSSHarvester($source_id);
    }
}

// ====================================================================================
// BOT Worker
// ====================================================================================

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

	    if($error_counter > 5) {
	        doQuery("UPDATE Bots SET isEnable=0 WHERE ID=:bot_id;", array(":bot_id" => $bot_id));
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
	}
    }
}

$log->debug("Bot worker END");

?>