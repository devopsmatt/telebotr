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

require 'vendor/autoload.php';

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

// create a log channel
$log = new Logger('telebotr');
$log->pushHandler(new StreamHandler('php://stderr', Logger::DEBUG));
// $log->pushHandler(new StreamHandler(__DIR__.'/logs/system.log', Logger::DEBUG));

include_once __DIR__.'/config.inc.php';

if((function_exists('locale_accept_from_http'))&&(isset($_SERVER['HTTP_ACCEPT_LANGUAGE']))) {
    $gbLang = locale_accept_from_http($_SERVER['HTTP_ACCEPT_LANGUAGE']);
} else if(isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
    $gbLang = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
} else {
    $gbLang = "en_US"; /* Default */
}

$sessionId = session_id();

if(empty($sessionId)) {
    session_start();
    $sessionId = session_id();
}

$DB = OpenDB();

function is_cli() {
    return (php_sapi_name() === 'cli');
}

// If run from CLI, doesn't need sessions...
if(!is_cli()) {
    // Initialize global session variables
    $mySession = new Session($sessionId);
    if($mySession->isLogged()) {
	$myUser = new User($mySession->userId);
    }
}

function doQuery($query,$params=array()) {
    global $DB, $log;

    try {
	$prepare = $DB->prepare($query);
	if(empty($params)) {
    	    $prepare->execute();
	} else {
	    $prepare->execute($params);
	}
	return $prepare;
    } catch(PDOException $e) {
	$log->error("DB query error! $query => ".$e->getMessage());
	return false;
    }
}

function OpenDB() {
    global $CFG, $log;

    $connectionString = 'mysql:host='.$CFG["dbHost"].';dbname='.$CFG["dbName"];

    try {
	//Connect to database.
	$db = new PDO($connectionString, $CFG["dbUser"], $CFG["dbPassword"]);
    } catch(PDOException $e) {
	$log->error("DB connect error! ".$e->getMessage());
	return false;
    }
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    return $db;
}

function isSelected($value,$match) {
    if($value == $match) return "selected";
}

function isChecked($value) {
    if($value) return "checked";
}

function getClientIP() {
    if(getenv('HTTP_X_FORWARDED_FOR')) {
	return getenv('REMOTE_ADDR')." (".getenv('HTTP_X_FORWARDED_FOR').")";
    } else {
	return getenv('REMOTE_ADDR');
    }
}

function cleanInput($u_Input) {
    $banlist = array (
	" insert ", " select ", " update ", " delete ", " distinct ", " having ", " truncate ", " replace ",
	" handler ", " like ", " as ", " or ", " procedure ", " limit ", " order by ", " group by ", " asc ", " desc "
    );
    $replacelist = array (
	" ins3rt ", " s3lect ", " upd4te ", " d3lete ", " d1stinct ", " h4ving ", " trunc4te ", " r3place ",
	" h4ndler ", " l1ke ", " 4s ", " 0r ", " procedur3 ", " l1mit ", " 0rder by ", " gr0up by ", " 4sc ", " d3sc "
    );
    if(preg_match( "/([a-zA-Z0-9])/", $u_Input )) {
	$u_Input = trim(str_replace($banlist, $replacelist, $u_Input));
    } else {
	$u_Input = NULL;
    }
    return $u_Input;
}

function APG($nChar=5) {
    $salt = "abcdefghjkmnpqrstuvwxyzABCDEFGHJKMNPQRSTUVWXYZ0123456789";
    srand((double)microtime()*1000000); 
    $i = 0;
    $pass = '';
    while ($i <= $nChar) {
	$num = rand() % strlen($salt);
        $tmp = substr($salt, $num, 1);
        $pass = $pass . $tmp;
        $i++;
    }
    return $pass;
}

function isURLAvailable($url) {
    //check, if a valid url is provided
    if(!filter_var($url, FILTER_VALIDATE_URL)) {
	return false;
    }

    //initialize curl
    $curlInit = curl_init($url);
    curl_setopt($curlInit,CURLOPT_CONNECTTIMEOUT,10);
    curl_setopt($curlInit,CURLOPT_HEADER,true);
    curl_setopt($curlInit,CURLOPT_NOBODY,true);
    curl_setopt($curlInit,CURLOPT_RETURNTRANSFER,true);

    //get answer
    $response = curl_exec($curlInit);

    $httpCode = curl_getinfo($curlInit, CURLINFO_HTTP_CODE);

    curl_close($curlInit);

    if($httpCode == 404) {
	// 404 
	return false;
    } else {
	return true;
    }
}

function isEmailValid($email) {
    return !!filter_var($email, FILTER_VALIDATE_EMAIL);
}

function getExcerpt($string, $length=55) {
    $suffix = '&hellip;';
    $text = trim(str_replace(array("\r","\n", "\t"), ' ', strip_tags($string)));

    $words = explode(' ', $text, $length + 1);
    if (count($words) > $length) {
        array_pop($words);
        array_push($words, '[...]');
        $text = implode(' ', $words);
    }
    return $text;
}

function sanitizeString($text) {
    $strFind = array('<<','>>','<','>');
    $strReplace = array('⟪','⟫','〈','〉');

    $text = html_entity_decode($text, ENT_COMPAT, 'UTF-8');
    $text = str_replace($strFind,$strReplace,$text);
    return $text;
}

function getTag($tag_id) {
    $result = doQuery("SELECT Tag FROM Tags WHERE ID=:tag_id;", array(":tag_id" => $tag_id));
    if($result) {
	$row = $result->fetch(PDO::FETCH_ASSOC);
	return preg_replace('/[^\x20-\x7E]/','', $row["Tag"]);
    } else {
	return false;
    }
}


class Session {
    var $ID;
    var $userId=false;
    var $AVP=array();
    var $Notices=array();
    var $IP;

    function __construct($ID) {
	/* Cancella tutte le sessioni piu vecchie di 1 ora */
	doQuery("DELETE FROM Sessions WHERE HOUR(TIMEDIFF(NOW(),lastAction)) > 1;");

	/* Create or refresh session data */
	$this->ID = $ID;
	$this->IP = getClientIP();

	doQuery("INSERT INTO Sessions(ID,IP,lastAction) VALUES (:ID,:IP,NOW()) ON DUPLICATE KEY UPDATE lastAction=NOW();",array(":ID" => $ID,":IP" => $this->IP));

	$result = doQuery("SELECT AVP FROM Sessions WHERE ID=:ID;",array(":ID" => $ID));
	if($result->rowCount() > 0) {
	    $row = $result->fetch(PDO::FETCH_ASSOC);
	    $this->AVP = unserialize($row["AVP"]);
	}

	$this->userId = $this->getAVP("userId");

	$this->Notices = unserialize($_SESSION["Notices"]);
    }

    function __destruct() {
	doQuery("UPDATE Sessions SET AVP=:AVP,lastAction=NOW() WHERE ID=:ID",array(":AVP" => serialize($this->AVP),":ID" => $this->ID));
	$_SESSION["Notices"] = serialize($this->Notices);
    }

    function isLogged() {
	if($this->userId > 0) {
	    return true;
	} else {
	    return false;
	}
    }

    function sendNotice($notice,$level="info") {
	$this->Notices[] = array("notice" => $notice, "level" => $level);
    }

    function getNotice() {
	return array_pop($this->Notices);
    }

    function getAVP($key) {
	if(array_key_exists($key,$this->AVP)) {
    	    return $this->AVP[$key];
	} else {
	    return false;
	}
    }

    function setAVP($key,$value) {
	if($value) {
	    $this->AVP[$key] = $value;
	} else {
	    unset($this->AVP[$key]);
	}
    }

    function getNonce() {
	$nonce = md5(APG(10));
	$this->setAVP('nonce',$nonce);
	return $nonce;
    }

    function checkNonce($nonce) {
	$tmp_nonce = $this->getAVP('nonce');
	if(strcmp($nonce,$tmp_nonce)==0) {
	    return true;
	} else {
	    return false;
	}
    }
}

class Channel {
    var $ID;
    var $botId;
    var $Type;
    var $Title;
    var $userId;
    var $AVP;
    var $isEnable;
    var $addDate;
    var $chgDate;

    function __construct($ID) {
	$result = doQuery("SELECT botId, Type, Title, AVP, isEnable, addDate, chgDate FROM Chats WHERE ID=:ID;",array(":ID" => $ID));
	if($result) {
	    $row = $result->fetch(PDO::FETCH_ASSOC);
	    $this->ID = $ID;
	    $this->botId = $row["botId"];
	    $this->Type = $row["Type"];
	    $this->Title = $row["Title"];

	    if($row["AVP"]) {
	        $this->AVP = unserialize($row["AVP"]);
	    }

	    $this->isEnable = $row["isEnable"];

	    $this->addDate = new DateTime($row["addDate"]);
	    $this->chgDate = new DateTime($row["chgDate"]);
	}
    }
}

class User {
    var $ID;
    var $Name;
    var $Email;
    var $loginETA;
    var $AVP=array();
    var $isEnable=false;

    function __construct($ID) {
	$result = doQuery("SELECT ID,Name,Email,AVP,isEnable,DATEDIFF(NOW(),lastLogin) AS loginETA FROM Users WHERE ID=:ID;",array(":ID" => $ID));
	if($result) {
	    $row = $result->fetch(PDO::FETCH_ASSOC);
	    $this->ID = $row["ID"];
	    $this->Name = stripslashes($row["Name"]);
	    $this->Email = stripslashes($row["Email"]);
	    $this->isEnable = $row["isEnable"];
	    $this->loginETA = $row["loginETA"];
	    if($row["AVP"]) {
		$this->AVP = json_decode($row["AVP"],true);
	    }
	}
    }

    function __destruct() {
	// Aggiorna AVP
	doQuery("UPDATE Users SET AVP=:AVP WHERE ID=:ID;",array(":AVP" => json_encode($this->AVP), ":ID" => $this->ID));
    }

    public function isAdmin() {
	return true; // #TODO
    }

    public function getAVP($key) {
    	return $this->AVP[$key];
    }

    public function setAVP($key,$value) {
	$this->AVP[$key] = $value;
    }
}

class Bot {
    var $ID;
    var $userId;
    var $Name;
    var $botToken;
    var $isEnable;
    var $errorCounter;
    var $lastError;
    var $publishDelay;
    var $maxETA;
    var $lastPublish;
    var $addDate;

    function __construct($ID) {
        $result = doQuery("SELECT ID, userId, Name, botToken, isEnable, publishDelay, maxETA, errorCounter, lastError, lastPublish, addDate FROM Bots WHERE ID=:ID;", array(":ID" => $ID));
	if($result) {
	    $row = $result->fetch(PDO::FETCH_ASSOC);

	    $this->ID = $ID;
	    $this->userId = $row["userId"];
	    $this->Name = stripslashes($row["Name"]);
	    $this->botToken = stripslashes($row["botToken"]);
	    $this->errorCounter = intval($row["errorCounter"]);
	    $this->lastError = stripslashes($row["lastError"]);
	    $this->isEnable = $row["isEnable"];
	    $this->publishDelay = $row["publishDelay"];
	    $this->maxETA = $row["maxETA"];
	    if(empty($row["lastPublish"])) {
		$this->lastPublish = false;
	    } else {
		$this->lastPublish = new DateTime($row["lastPublish"]);
	    }
	    $this->addDate = new DateTime($row["addDate"]);
	} else {
	    return false;
	}
    }

}

class Source {
    var $ID;
    var $chatId;
    var $userId;
    var $Name;
    var $Description;
    var $URL;
    var $AVP;
    var $filterBy;
    var $isPublic;
    var $addDate;

    function __construct($ID) {
        $result = doQuery("SELECT ID, chatId, userId, Name, Description, URL, AVP, filterBy, isPublic, addDate FROM Sources WHERE ID=:ID;",array(":ID" => $ID));
	if($result) {
	    $row = $result->fetch(PDO::FETCH_ASSOC);
	    $this->ID = $ID;
	    $this->chatId = $row["chatId"];
	    $this->userId = $row["userId"];
	    $this->Name = stripslashes($row["Name"]);
	    $this->Description = stripslashes($row["Description"]);
	    $this->URL = stripslashes($row["URL"]);
	    $this->isPublic = $row["isPublic"];
	    $this->addDate = new DateTime($row["addDate"]);

	    if($row["filterBy"]) {
		$this->filterBy = json_decode($row["filterBy"], true);
	    }

	    if($row["AVP"]) {
		$this->AVP = json_decode($row["AVP"],true);
	    } else {
		$this->AVP = array();
	    }
	} else {
	    return false;
	}
    }

    public function getAVP($key) {
	if(in_array($key,$this->AVP)) {
    	    return $this->AVP[$key];
	} else {
	    return false;
	}
    }

    public function setAVP($key,$value) {
	$this->AVP[$key] = $value;
	// Aggiorna AVP
	doQuery("UPDATE Sources SET AVP=:AVP WHERE ID=:ID;",array(":AVP" => json_encode($this->AVP), ":ID" => $this->ID));
    }

}

function postAddTag($post_id,$tag) {
    global $DB;
    $tag = strtolower(trim($tag));
    
    if(strlen($tag) > 0) {
	$result = doQuery("SELECT ID FROM Tags WHERE Tag LIKE :tag;",array(":tag" => $tag));
	if($result->rowCount() > 0) {
	    $row = $result->fetch(PDO::FETCH_ASSOC);
	    $tag_id = $row["ID"];
	} else {
	    doQuery("INSERT INTO Tags(Tag) VALUES (:tag);", array(":tag" => $tag));
	    $tag_id = $DB->lastInsertId();
	}
	doQuery("INSERT INTO PostTags(postId,tagId,addDate) VALUES (:post_id,:tag_id,NOW());", array(":post_id" => $post_id, ":tag_id" => $tag_id));
	return true;
    } else {
	return false;
    }
}

class Post {
    var $ID;
    var $Title;
    var $Excerpt;
    var $Author;
    var $imageUrl;
    var $URL;
    var $addDate;
    var $publishDate;
    var $isPublished;
    var $isActive;
    var $Views;
    var $sourceId;
    var $chatId;

    function __construct($ID) {
        $result = doQuery("SELECT sourceId, chatId, Title, Excerpt, Author, ImageURL, URL, Views, isPublished, isActive, addDate, publishDate FROM Posts WHERE ID=:ID;", array(":ID" => $ID));
	if($result) {
	    $row = $result->fetch(PDO::FETCH_ASSOC);

	    $this->Title = strip_tags($row["Title"]);
	    $this->Excerpt = strip_tags($row["Excerpt"]);
	    $this->Author = $row["Author"];
	    $this->imageUrl = $row["ImageURL"];
	    $this->URL = $row["URL"];
	    $this->Views = $row["Views"];
	    $this->isPublished = $row["isPublished"];
	    $this->isActive = $row["isActive"];
	    $this->addDate = new DateTime($row["addDate"]);

	    if(is_null($row["publishDate"])) {
		$this->publishDate = NULL;
	    } else {
		$this->publishDate = new DateTime($row["publishDate"]);
	    }

	    $this->ID = $ID;
	    $this->sourceId = $row["sourceId"];
	    $this->chatId = $row["chatId"];
	}
    }

    function setPublished() {
	$this->isPublished = true;
	/* Set post published... */
	doQuery("UPDATE Posts SET isPublished=1, publishDate=NOW() WHERE ID=:ID;", array(":ID" => $this->ID));
    }

    function getAddDate($format='Y-m-d H:i:s') {
	return $this->addDate->format($format);
    }

    function getAuthor() {
	$source = new Source($this->sourceId);
	if($source->getAVP('showAuthor') === true) {
	    return $this->Author;
	} else {
	    return $source->Name;
	}
    }
    
    function getURL() {
	global $CFG;

	$source = new Source($this->sourceId);
	if($source->getAVP("urlRewrite") === true) {
	    return $CFG["baseUrl"]."/post/$this->ID";
	} else {
	    return $this->URL;
	}
    }
}

function RSSHarvester($source_id) {
    global $DB, $log;

    $source = new Source($source_id);

    $chat_id = $source->chatId;
    $user_id = $source->userId;

    if(empty($chat_id)) {
	return false;
    }

    if(isURLAvailable($source->URL)) {
	try {
	    $feed = new SimplePie();
    	    $feed->set_feed_url($source->URL);
	    $feed->set_cache_location(__DIR__.'/temp');
	    $feed->set_cache_duration(3600);
    	    $feed->init();
	    $feed->handle_content_type();
	} catch (Exception $e) {
	    $log->error("Exception: ".$e->getMessage());
	    return false;
	}
        if ($feed->error()) {
    	    $log->error("Error while harvesting source URL ".$source->URL." (ID $source_id): ".$feed->error());
	    return false;
	}

	$max = $feed->get_item_quantity();

	if ($max > 0) {
	    for ($x = 0; $x < $max; $x++) {

		$item = $feed->get_item($x);

	        $title = $item->get_title();
		$excerpt = getExcerpt($item->get_content());
		$url = $item->get_permalink();
		$categories = array();

		if($author = $item->get_author()) {
	    	    $author = $author->get_name();
		} else {
		    $author = NULL;
		}

	        $hash = sha1($title.$excerpt);

		if(is_cli()) {
		    echo "$title - by $author\n$url\n";
		}

		foreach ((array) $item->get_categories() as $category) {
		    if(is_cli()) {
			echo "category: ".$category->get_label()."\n";
		    }
		    $categories[] = $category->get_label();
		}

		if ($enclosure = $item->get_enclosure()) {
    	    	    $image_url = $enclosure->get_thumbnail();
		} else {
	    	    $image_url = NULL;
		}

		$isValid = true;

		if(is_array($source->filterBy)&&(count($source->filterBy) > 0)) {
		    /* Check filters */
		    foreach($source->filterBy as $key => $value) {
			switch($key) {
			    case 'title':
				$isValid = false;
				foreach($value as $val) {
				    if(preg_match("/$val/i", $title)) {
					$isValid = true;
				    }
				}
				break;
			    case 'category':
				if(count($categories) > 0) {
				    $isValid = false;
				    foreach($categories as $category) {
					foreach($value as $val) {
					    if(preg_match("/$val/i", $category)) {
						$isValid = true;
					    }
					}
				    }
				}
				break;
			    case 'degoogling':
				$isValid = false;
				$re = '/.*url=(http.*)&amp;ct=.*/m';
				if(preg_match($re, $url, $matches)) {
				    $url = $matches[1];
				    $isValid = true;
				}
			    case 'url-blacklist':
				$isValid = true;
				foreach($value as $val) {
				    // If $url contain $val, set isValid to false
				    if(preg_match("/$val/i", $url)) {
					$isValid = false;
				    }
				}
				break;
			    default:
				break;
			}
		    }
		}

		if($isValid) {
		    /* ...compare with posts published to avoid duplicates. */
		    $result = doQuery("SELECT ID FROM Posts WHERE (Hash=:hash OR URL=:url) AND chatId=:chat_id;", array(":hash" => $hash, ":url" => $url, ":chat_id" => $chat_id));
		    if($result->rowCount() == 0) {
	    		/* Add post to posts queue... */
	    		doQuery("INSERT INTO Posts (userId,chatId,sourceId,Title,Excerpt,Author,ImageURL,URL,Hash,isActive,addDate) VALUES (:user_id,:chat_id,:source_id,:title,:excerpt,:author,:image_url,:url,:hash,1,NOW());", array(":user_id" => $user_id,":chat_id" => $chat_id, ":source_id" => $source_id, ":title" => $title, ":excerpt" => $excerpt,":author" => (is_null($author)?"NULL":$author),":image_url" => (is_null($image_url)?"NULL":$image_url), ":url" => $url, ":hash" => $hash));
	    		$post_id = $DB->lastInsertId();

	    		if (count($categories) > 0) {
	    		    foreach ($categories as $category) {
		    		postAddTag($post_id,$category);
			    }
			}
			doQuery("UPDATE Sources SET chgDate=NOW() WHERE ID=:source_id;", array(":source_id" => $source_id));
		
			$log->info("Post $post_id added to CHAT $chat_id queue...");
		    }
		}
	    } 
	}
	return true;
    } else {
	return false;
    }
}

?>