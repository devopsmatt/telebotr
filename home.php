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

include "common_header.php";

if(!$mySession->isLogged()) {
    header('Location: /');
    exit();
}

$bots_id = array();

$result = doQuery("SELECT ID FROM Bots WHERE userId=:user_id;", array(":user_id" => $mySession->userId));
if($result) {
    while($row = $result->fetch(PDO::FETCH_ASSOC)) {
	$bots_id[] = $row["ID"];
    }
}

?>
<div class="container-fluid"><!-- CONTAINER -->
    <div class="row">
	<div class="col-sm-3 col-md-2 sidebar">
	    <?php include "common_leftmenu.php"; ?>
	</div>
        <div class="col-sm-9 col-md-10 main" id="mainContent"><!-- MAIN -->
	    <h1><?php echo _("Dashboard"); ?></h1>

            <div class="row">
                <div class="col-lg-3 col-md-6">
                    <div class="card card-inverse card-success">
                        <div class="card-block bg-success">
                            <div class="rotate">
                                <i class="fa fa-user fa-5x"></i>
                            </div>
                            <h6 class="text-uppercase"><?php echo _("Bots"); ?></h6>
                            <h1 class="display-1">
			    <?php 
				echo count($bots_id);
			    ?>
			    </h1>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="card card-inverse card-danger">
                        <div class="card-block bg-danger">
                            <div class="rotate">
                                <i class="fa fa-list fa-4x"></i>
                            </div>
                            <h6 class="text-uppercase"><?php echo _("Posts"); ?></h6>
                            <h1 class="display-1">
			    <?php
			    $result = doQuery("SELECT ID FROM Posts WHERE userId=:user_id;",array(":user_id" => $mySession->userId));
			    if($result) {
				echo $result->rowCount();
			    }
			    ?>
			    </h1>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="card card-inverse card-info">
                        <div class="card-block bg-info">
                            <div class="rotate">
                                <i class="fa fa-users fa-5x"></i>
                            </div>
                            <h6 class="text-uppercase"><?php echo _("Sources"); ?></h6>
                            <h1 class="display-1">
			    <?php
			    $result = doQuery("SELECT ID FROM Sources WHERE userId=:user_id;",array(":user_id" => $mySession->userId));
			    if($result) {
				echo $result->rowCount();
			    }
			    ?>
			    </h1>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="card card-inverse card-warning">
                        <div class="card-block bg-warning">
                            <div class="rotate">
                                <i class="fa fa-check-square-o fa-5x"></i>
                            </div>
                            <h6 class="text-uppercase"><?php echo("Views"); ?></h6>
                            <h1 class="display-1">
			    <?php
			    $result = doQuery("SELECT SUM(Views) AS Views FROM Posts WHERE userId=:user_id AND TIMESTAMPDIFF(DAY,NOW(),addDate) < 7;",array(":user_id" => $mySession->userId));
			    if($result) {
				$row = $result->fetch(PDO::FETCH_ASSOC);
				echo $row["Views"];
			    }
			    ?>
			    </h1>
                        </div>
                    </div>
                </div>
            </div>
	    <hr>
	    <h4><i class="fa fa-list" aria-hidden="true"></i> <?php echo _("Latest posts"); ?></h4>
	    <table class="table table-hover table-striped">
		<thead>
		    <tr>
			<th data-column-id="status"></th>
			<th data-column-id="title">Title</th>
			<th data-column-id="source">Source</th>
			<th data-column-id="views">Views</th>
			<th data-column-id="addDate">addDate</th>
			<th data-column-id="publishDate">publishDate</th>
		    </tr>
		</thead>
		<tbody>
<?php
$result = doQuery("SELECT ID FROM Posts WHERE userId=:user_id ORDER BY addDate DESC LIMIT 20;", array(":user_id" => $mySession->userId));
if($result) {
    while($row = $result->fetch(PDO::FETCH_ASSOC)) {
	$post = new Post($row["ID"]);
	echo "<tr ".($post->isActive?"":"table-warning")." data-href=\"/ajax/postDetails\"  data-id=\"$post->ID\">
	    <td>";
	if($post->isPublished) {
	    echo "<i class=\"fa fa-send-o\" aria-hidden=\"true\">";
	} else if($post->isActive) {
	    echo "<i class=\"fa fa-clock-o\" aria-hidden=\"true\">";
	} else {
	    echo "<i class=\"fa fa-pause\" aria-hidden=\"true\">";
	}
	$source = new Source($post->sourceId);
	echo "</td>
	    <td>$post->Title</td>
	    <td>$source->Name</td>
	    <td>$post->Views</td>
	    <td>".$post->addDate->format('H:m m-d-Y')."</td>
	    <td>".(is_null($post->publishDate) ? "not yet":$post->publishDate->format('H:m m-d-Y'))."</td>
	</tr>";
    }
}
?>
		</tbody>
	    </table>
	</div><!-- /MAIN -->
    </div>
</div><!-- /CONTAINER -->
<?php

include "common_footer.php";

?>
