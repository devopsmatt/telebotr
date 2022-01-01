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


include __DIR__."/common.inc.php";

$post_id = intval($_GET["id"]);

$result = doQuery("SELECT URL,TIMESTAMPDIFF(HOUR,lastCheck,NOW()) AS lastCheck,isActive FROM Posts WHERE ID=:ID;",array(":ID" => $post_id));
if($result) {
    $row = $result->fetch(PDO::FETCH_ASSOC);

    $URL = stripslashes($row["URL"]);
    $isActive = $row["isActive"];
    $lastCheck = intval($row["lastCheck"]);

    if((!$lastCheck)||($lastCheck > 6)) {
	$isAvailable = isURLAvailable($URL);

	if(!$isAvailable) {
	    $isActive=false;
	} else {
	    $isActive=true;
	}
	doQuery("UPDATE Posts SET lastCheck=NOW(),isActive=:isActive WHERE ID=:ID;",array(":ID" => $post_id,":isActive" => $isActive));
    }

    if($isActive) {
	// Increase view counter...
	doQuery("UPDATE Posts SET Views=Views+1 WHERE ID=:ID;",array(":ID" => $post_id));
	// Redirect !
	header("Location: $URL", TRUE, 307);
    } else {
	// Ooops, invalid URL: redirect to abuse page !
	include __DIR__."/common_header.php";
?>
<div class="container">
    <div class="row">
	<div class="clearfix">&nbsp;</div>
	<h1>Ooops !</h1>
	<p>The URL <i><?php echo $URL; ?></i> you are trying to visit seems in trouble now. Please try clicking on the following link or, if still dont work, try again in few minutes...</p>
	<div class="center-block">
	    <a class="btn btn-danger" href="<?php echo $URL; ?>"><?php echo $URL; ?></a>
	</div>
    </div>
</div>

<?php
	include __DIR__."/common_footer.php";
    }
} else {
    echo "Invalid ID";
}

?>