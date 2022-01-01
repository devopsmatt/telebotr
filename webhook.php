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

require_once __DIR__.'/bot/TeleBot.php';

$key = cleanInput($_GET["key"]);

if(strlen($key) > 0) {
    $result = doQuery("SELECT ID,botToken FROM Bots WHERE isEnable=1 AND ID=:key;",array(":key" => $key));
    if($result) {
	$row = $result->fetch(PDO::FETCH_ASSOC);

	$botToken = $row["botToken"];
	$botId = $row["ID"];

	$bot = new TeleBot($botId, $botToken, 'TeleBotChat');

	$botWebhook = $CFG["baseUrl"]."/webhook/$key";

 	$bot->setWebhook($botWebhook);

	$response = file_get_contents('php://input');
	$update = json_decode($response, true);

	$log->info("Webhook - Got update for botId $botId");

	$bot->init();
	$bot->onUpdateReceived($update);

	echo $update;
    } else {
	echo "Invalid ID";
    }
} else {
    echo "Bot ID not set";
}
