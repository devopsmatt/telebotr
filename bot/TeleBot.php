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


require_once __DIR__.'/TelegramBot.php';

require_once __DIR__.'/../common.inc.php';

class TeleBot extends TelegramBot {
    public function init() {
	parent::init();
    }
}

class TeleBotChat extends TelegramBotChat {

    protected $ID=false;
    protected $botId;
    protected $chatId;

    public function __construct($core, $bot_id, $chat_id, $chat_type='private') {
	parent::__construct($core, $bot_id, $chat_id, $chat_type);

	$this->botId = $bot_id;
	$this->chatId = $chat_id;
    }

    public function init() {
	
    }

    public function on_poll() {
	global $DB;
	global $log;

	$log->debug("on_poll(Bot $this->botId on $this->chatType id $this->chatId)");

	$tmpBot = new Bot($this->botId);

	// Now send messages via BOT to this channel...
	$result = doQuery("SELECT ID FROM Posts WHERE chatId=:chatId AND isPublished=0 AND isActive=1 AND TIMESTAMPDIFF(HOUR,NOW(),addDate) < 12 ORDER BY RAND() LIMIT 1;",array(":chatId" => $this->chatId));
	if($result) {
	    while($row = $result->fetch(PDO::FETCH_ASSOC)) {
		$post_id = intval($row["ID"]);

		$post = new Post($post_id);

		$msg = "<b>".sanitizeString($post->Title)."</b>\n".sanitizeString($post->Excerpt)."\n\nby ".sanitizeString($post->getAuthor())." - ".$post->getURL();

		$response = $this->apiSendMessage($msg);
		if($response['ok'] === true) {
		    $log->debug("Bot ID $this->botId just send post ID $post_id to chat ID $this->chatId");
		    doQuery("UPDATE Bots SET errorCounter=0,lastPublish=NOW() WHERE ID=:ID;", array(":ID" => $this->botId));
		    $post->setPublished();
		} else {
		    $log->error($response['error_code']." while bot ID $this->botId try to send post ID $post_id to chat ID $this->chatId: ".$response['description']);
		    doQuery("UPDATE Bots SET errorCounter=errorCounter+1,lastError=:error WHERE ID=:ID;", array(":error" => $response['error_code']." - ".$response['description'],":ID" => $this->botId));
		}
	    }
	}
    }
    
    /* ===== HELP ===== */
    public function command_help($params, $message) {
	$this->apiSendMessage("BotID:".$this->botId." Chat ID:".$this->chatId." Type:".$this->chatType);
    }

    public function bot_added_to_chat($message) {
	global $log;
	$log->info("Bot ID $this->botId added to ".$message['chat']['type']." ".$message['chat']['title']);
    }

    public function bot_kicked_from_chat($message) {
	global $log;
	$log->info("Bot ID $this->botId kicked from ".$message['chat']['type']." ".$message['chat']['title']);
    }

    public function some_command($command, $params, $message) {
	global $log;
	$log->info("Bot ID $this->botId receive command from ".$message['chat']['type']." ".$message['chat']['title'].": ".$message['text']);
    }

    public function message($text, $message) {
	global $log;
	$log->info("Bot ID $this->botId receive from ".$message['chat']['type']." ".$message['chat']['title'].": ".$message['text']);
    }
}