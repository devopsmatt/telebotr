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

if(isset($_GET["action"])) {
    $ajaxAction = cleanInput($_GET["action"]);
} else {
    $ajaxAction = cleanInput($_POST["action"]);
}

/* ===========================================
doPoll() for async notify messages
=========================================== */
if($ajaxAction == "doPoll") {
    $notice = $mySession->getNotice();
    if($notice) {
	echo json_encode($notice);
    }
}
/* ==========================================
LOGGED ONLY CALLBACKS
============================================ */
if($mySession->isLogged()) {
    if($ajaxAction == "postDetails") {
	$postId = intval($_POST["id"]);
	$myPost = new Post($postId);
	if($myPost) {
	    $retArray["post_id"] = $postId;
	    $retArray["title"] = $myPost->Title;
	    $retArray["body"] = $myPost->Excerpt;
	    echo json_encode($retArray);
	}
    }

}

?>
