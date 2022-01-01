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

include_once __DIR__."/common.inc.php";

if(isset($_GET["action"])) {
    $cbAction = cleanInput($_GET["action"]);
} else if(isset($_POST["action"])) {
    $cbAction = cleanInput($_POST["action"]);
} else {
    $cbAction = false;
}

// ======================================================================== NOT LOGGED CALLBACKS
if(!$mySession->isLogged()) {
    if($cbAction == "userLogin") {

	$nonce = $_POST["nonce"];
	if($mySession->checkNonce($nonce)) {
	    $userEmail = $_POST["userEmail"];
	    $userPassword = $_POST["userPassword"];

	    $result = doQuery("SELECT ID FROM Users WHERE Email=:email AND Password=PASSWORD(:password);",array(":email" => $userEmail, ":password" => $userPassword));
	    if(($result)&&($result->rowCount() > 0)) {
		$row = $result->fetch(PDO::FETCH_ASSOC);
		$mySession->setAVP('userId',$row["ID"]);
		$mySession->sendNotice("Welcome $userEmail!", "success");
		$log->info("User $userMail LOGGED IN from $mySession->IP");
		header('Location: /');
	    } else {
		$mySession->sendNotice("Invalid credentials", "error");
	    }
	} else {
	    $mySession->sendNotice("Invalid NONCE", "error");
	}
    }
// ======================================================================== LOGGED CALLBACKS
} else {
    // ===================================================================== LOGOUT
    if($cbAction == "userLogout") {
	$log->info("User ".$mySession->userId." logged out");
	$mySession->userId=false;
	$mySession->sendNotice("Logged out");
	header('Location: /');
	exit();
    }
    /* ===========================================
    Add or edit source
    =========================================== */
    if($cbAction == "addSource") {
	$source_url = $_POST["sourceUrl"];

	if(isURLAvailable($source_url)) {
	    if(isset($_POST["isPublic"]) && $_POST["isPublic"] == "on") {
		$is_public = 1;
	    } else {
		$is_public = 0;
	    }
	    $source_name = mysqli_real_escape_string($DB,$_POST["sourceName"]);
	    $source_description = mysqli_real_escape_string($DB,$_POST["sourceDescription"]);

	    $source_apikey =createApiKey();

	    doQuery("INSERT INTO Sources(Type,userId,Name,Description,URL,apiKey,isPublic,addDate) VALUES ('2','$myUser->ID','$source_name','$source_description','$source_url','$source_apikey','$is_public',NOW());");
	    $source_id = mysqli_insert_id($DB);

	    $mySession->sendNotice("Added new source ID $source_id from URL $source_url","success");
	} else {
	    $mySession->sendNotice("Oops ! URL $source_url seems to be not available: please check and try again.","error");
	}
    }

    if($cbAction == "editSource") {
	$source_id = cleanInput($_POST["sourceId"]);

	$source_name = mysqli_real_escape_string($DB,$_POST["sourceName"]);
	$source_description = mysqli_real_escape_string($DB,$_POST["sourceDescription"]);

	$source_url = mysqli_real_escape_string($DB,$_POST["sourceUrl"]);

	$source_apikey = mysqli_real_escape_string($DB,$_POST["sourceApiKey"]);

	$source_bot_id = intval($_POST["sourceBot"]);

	$source = new Source($source_id);
	if($source) {
	    if(isset($_POST["isPublic"]) && $_POST["isPublic"] == "on") {
		$is_public = 1;
	    } else {
		$is_public = 0;
	    }

	    if(isset($_POST["isMaskAuthor"])) {
		if($_POST["isMaskAuthor"] == "on") {
		    $source->setACL("maskAuthor",true);
		} else {
		    $source->setACL("maskAuthor",false);
		}
	    }

	    if(isset($_POST["isStrictIp"]) && $_POST["isStrictIp"] == "on") {
	        $is_strict_ip = 1;
	    } else {
	        $is_strict_ip = 0;
	    }

	    if(isset($_POST["deleteThis"]) && $_POST["deleteThis"] == "on") { /* Delete this BOT */
		doQuery("DELETE FROM Sources WHERE ID='$source_id';");
		LOGWrite($_SERVER["SCRIPT_NAME"], "Blog $source->Name ($source_id) owned by user $myUser->displayName was DELETED");

		$mySession->sendNotice("Your source $source->Name was deleted successfully !","success");
	    } else {
		doQuery("UPDATE Sources SET Name='$source_name',Description='$source_description',URL='$source_url',botId='$source_bot_id',apiKey='$source_apikey',isPublic='$is_public',isStrictIp='$is_strict_ip' WHERE ID='$source_id';");
		LOGWrite($_SERVER["SCRIPT_NAME"], "Source $source->Name ($source_id) owned by user $myUser->displayName was UPDATED");

		$mySession->sendNotice("Your source $source->Name was updated successfully !","success");
	    }
	}
    }

    /* ===========================================
    Add/Edit BOT
    =========================================== */
    if($cbAction == "editBot") {
	if(intval($_POST["botId"]) > 0) {
	    $bot_id = cleanInput($_POST["botId"]);

	    $result = doQuery("SELECT ID FROM Bots WHERE userId='$myUser->ID' AND ID='$bot_id';");
	    if(mysqli_num_rows($result) > 0) {
		$bot_name = mysqli_real_escape_string($DB,cleanInput($_POST["botName"]));

		if(preg_match("/^(\d+):(\S+)$/", $_POST["botToken"],$bot_token)) { /* ID:Token */
		    $bot_id = $bot_token[1];
		    $bot_sha = $bot_token[2];

		    if(isset($_POST["isEnable"]) && $_POST["isEnable"] == "on") {
			$is_enable = 1;
		    } else {
			$is_enable = 0;
		    }

		    $bot_publish_delay = intval($_POST["botPublishDelay"]);
		    if($myUser->getACL("minBotPublishDelay") > $bot_publish_delay) {
			$bot_publish_delay = $myUser->getACL("minBotPublishDelay");
		    }

		    if(isset($_POST["deleteThis"]) && $_POST["deleteThis"] == "on") { /* Delete this BOT */
		        doQuery("DELETE FROM Bots WHERE ID='$bot_id';");
			LOGWrite($_SERVER["SCRIPT_NAME"], "Bot $bot_name ($bot_id) owned by user $myUser->displayName was DELETED");

			$mySession->sendNotice("Your BOT $bot_name was deleted successfully !","success");
		    } else {
			doQuery("UPDATE Bots SET Name='$bot_name',ID='$bot_id',botToken='$bot_id:$bot_sha',publishDelay='$bot_publish_delay',isEnable='$is_enable',errorCounter=0 WHERE ID='$bot_id';");
			LOGWrite($_SERVER["SCRIPT_NAME"], "Bot $bot_name ($bot_id) owned by user $myUser->displayName was UPDATED");

			$mySession->sendNotice("Your BOT $bot_name was updated successfully !","success");
		    }
		}
	    }
	} else {
	    // Add new BOT
	    $bot_name = mysqli_real_escape_string(cleanInput($_POST["botName"]));
	    if(preg_match("/^(\d+):(\S+)$/", $_POST["botToken"],$bot_token)) { /* ID:Token */
		$bot_id = $bot_token[1];
		$bot_sha = $bot_token[2];

		$bot_name = mysqli_real_escape_string($DB,cleanInput($_POST["botName"]));

		$result = doQuery("SELECT ID FROM Bots WHERE ID='$bot_id';");
	        if(mysqli_num_rows($result) > 0) {
		    $mySession->sendNotice("Oops ! Seems that BOT $bot_id was already added.","error");
		} else {
	    	    $bot_publish_delay = intval($_POST["botPublishDelay"]);
		    if($myUser->getACL("minBotPublishDelay") > $bot_publish_delay) {
			$bot_publish_delay = $myUser->getACL("minBotPublishDelay");
		    }

		    doQuery("INSERT INTO Bots(ID,userId,Name,botToken,publishDelay,addDate) VALUES ('$bot_id','$myUser->ID','$bot_name','$bot_id:$bot_sha','$bot_publish_delay',NOW());");
		    LOGWrite($_SERVER["SCRIPT_NAME"], "Added BOT token for user $myUser->displayName and Bot ID $bot_id");
	    
		    $mySession->sendNotice("Your new BOT Token was addedd successfully !","success");
		}
	    } else {
		$mySession->sendNotice("Bot token is not valid ! Should be something like 12345661:AbcdEfghIlMnOpqrsTuvzXy so check and try again.","error");
	    }
	}
    }

    /* ===========================================
    Edit channel
    =========================================== */
    if($cbAction == "editChannel") {
	$channel_id = cleanInput($_POST["channelId"]);
	
	$channel = new Channel($channel_id);
	if($channel) {
	    if(isset($_POST["isEnable"]) && $_POST["isEnable"] == "on") {
		$is_enable = 1;
	    } else {
		$is_enable = 0;
	    }

	    $channel_bot = cleanInput($_POST["channelBot"]);
	    $new_channel_id = cleanInput($_POST["newChannelID"]);

	    if(isset($_POST["deleteThis"]) && $_POST["deleteThis"] == "on") { /* Delete this Channel */
		doQuery("DELETE FROM Chats WHERE ID='$channel_id';");
		LOGWrite($_SERVER["SCRIPT_NAME"], "Channel $channel->ID owned by user $myUser->displayName was DELETED");

		$mySession->sendNotice("Your channel $channel->ID was deleted successfully !","success");
	    } else {
		if(strcmp($new_channel_id,$channel_id) != 0) {
		    doQuery("UPDATE Chats SET ID='$new_channel_id',botId='$channel_bot',isEnable='$is_enable' WHERE ID='$channel_id';");
		    LOGWrite($_SERVER["SCRIPT_NAME"], "Channel $channel->ID owned by user $myUser->displayName was UPDATED with new ID $new_channel_id");
		} else {
		    doQuery("UPDATE Chats SET isEnable='$is_enable',botId='$channel_bot' WHERE ID='$channel_id';");
		    LOGWrite($_SERVER["SCRIPT_NAME"], "Channel $channel->ID owned by user $myUser->displayName was UPDATED");
		}
		$mySession->sendNotice("Your channel $channel->ID was updated successfully !","success");
	    }
	}
    }

    /* ===========================================
    Add channel
    =========================================== */
    if($cbAction == "addChannel") {
	if(isset($_POST["isEnable"]) && $_POST["isEnable"] == "on") {
	    $is_enable = 1;
	} else {
	    $is_enable = 0;
	}

	$channel_bot = cleanInput($_POST["channelBot"]);
	$new_channel_id = cleanInput($_POST["newChannelID"]);

	if(strlen($new_channel_id) > 5) {
	    doQuery("INSERT INTO Chats(ID,Type,botId,isEnable,addDate) VALUES('$new_channel_id','channel','$channel_bot','$is_enable',NOW());");
	    LOGWrite($_SERVER["SCRIPT_NAME"], "Channel $new_channel_id wad added by user $myUser->displayName");

	    $mySession->sendNotice("Your new channel $new_channel_id was added successfully !","success");
	} else {
	    $mySession->sendNotice("Please check channel id $new_channel_id: seems to be invalid ! ","error");
	}
    }

    /* ===========================================
    Add/Edit Help
    =========================================== */
    if($cbAction == "editHelp") {
	$help_id = intval($_POST["helpId"]);
	$help_title = mysqli_real_escape_string($DB,cleanInput($_POST["helpTitle"]));
	$help_content = mysqli_real_escape_string($DB,cleanInput($_POST["helpContent"]));

	if($help_id > 0) {
	    doQuery("UPDATE Help SET Title='$help_title',Content='$help_content' WHERE ID='$help_id';");
	} else {
	    doQuery("INSERT INTO Help(Title,Content,addDate) VALUES ('$help_title','$help_content',NOW())");
	    $help_id = mysqli_insert_id($DB);
	}
	
	if(strlen($_POST["helpTags"]) > 0) {
	    foreach(explode(',',$_POST["helpTags"]) as $tag) {
		helpAddTag($help_id,$tag);
	    }
	}
    }

    /* ===========================================
    Add/Edit Blog Post
    =========================================== */
    if($cbAction == "editBlogPost") {
	$blog_post_id = intval($_POST["blogPostId"]);
	$blog_post_title = mysqli_real_escape_string($DB,$_POST["blogPostTitle"]);
	$blog_post_content = mysqli_real_escape_string($DB,$_POST["blogPostContent"]);

	if($blog_post_id > 0) {
	    doQuery("UPDATE Blog SET Title='$blog_post_title',Content='$blog_post_content' WHERE ID='$blog_post_id';");

	    $mySession->sendNotice("Post '$blog_post_title' updated succesfully !","success");
	} else {
	    doQuery("INSERT INTO Blog(Title,Content,addDate) VALUES ('$blog_post_title','$blog_post_content',NOW())");
	    $blog_post_id = mysqli_insert_id($DB);

	    $mySession->sendNotice("New post '$blog_post_title' added succesfully !","success");
	}
    }

    /* ===========================================
    Delete user account
    =========================================== */
    if($cbAction == "userDelete") {
	$user_id = intval($_POST["userId"]);
	
	$user = new User($user_id);
	if(isset($user->ID)) {
	    // Remove all Sources belonging to this user
	    doQuery("DELETE FROM Sources WHERE userId='$user_id';");
	    // Remove all BOTs 
	    doQuery("DELETE FROM Bots WHERE userId='$user_id';");
	    // Remove all POSTs
	    doQuery("DELETE FROM Posts WHERE userId='$user_id';");
	    // Finally, disable user account
	    doQuery("UPDATE Users SET isEnable=0 WHERE ID='$user_id';");
	    // ...and send eMail
	    $user->sendMail(getString("user-delete-mail-subject"),getString("user-delete-mail-body"));

	    $mySession->sendNotice("User $user->displayName deleted succesfully !","success");
	} else {
	    $mySession->sendNotice("Error deleting $user_id user","error");
	}
    }

    /* ===========================================
    Enable user
    =========================================== */
    if($cbAction == "userEnable") {
	$user_id = intval($_POST["userId"]);
	
	$user = new User($user_id);
	if($user) {
	    // Enable user account
	    doQuery("UPDATE Users SET isEnable=1 WHERE ID='$user_id';");
	}
    }
}

?>
