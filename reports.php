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

if($mySession->isLogged()) {
// LOGGED USERS
    $gbJsScripts = array("Chart.bundle.min.js","chart.ajax.js");
?>
<div class="container-fluid"><!-- CONTAINER -->
    <div class="row">
	<div class="col-sm-3 col-md-2 sidebar">
	    <?php include "common_leftmenu.php"; ?>
	</div>
        <div class="col-sm-9 col-md-10 main" id="mainContent"><!-- MAIN -->
	    <h1><?php echo _("Reports"); ?></h1>
	    <h3><?php echo _("Total views in last 7 days"); ?></h3>
	    <canvas id="myChart" width="400" height="200"></canvas>
	    <h3><?php echo _("Top 10 sources"); ?></h3>
	    <table class="table table-hover">
		<thead>
		    <tr>
			<th><?php echo _("Clicks"); ?></th>
			<th><?php echo _("Day"); ?></th>
			<th><?php echo _("Source"); ?></th>
		    </tr>
		</thead><tbody>

	    
<?php
	    $result = doQuery("SELECT Day,sourceId,numPosts,numClicks FROM SourceStats AS t1 INNER JOIN Sources AS t2 ON t1.sourceId=t2.ID WHERE t2.userId='$myUser->ID' AND Day >= DATE(NOW()) - INTERVAL 7 DAY ORDER BY numClicks DESC LIMIT 10;");
	    if(mysqli_num_rows($result) > 0) {
		while($row = mysqli_fetch_array($result,MYSQLI_ASSOC)) {
		    $day = new DateTime($row["Day"]);
		    $tmpSource = new Source($row["sourceId"]);
		    $numPosts = $row["numPosts"];
		    $numClicks = $row["numClicks"];
		    echo "<tr>
			<td>$numClicks</td>
			<td>".$day->format("d-m-Y")."</td>
			<td>$tmpSource->Name</td>
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
} else {
    header("Location: /");
}


include "common_footer.php";

?>
