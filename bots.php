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


include __DIR__."/common_header.php";

if(!$mySession->isLogged()) {
    header("Location: /");
    exit();
}
?>
<div class="container-fluid"><!-- CONTAINER -->
    <div class="row">
	<div class="col-sm-3 col-md-2 sidebar">
	    <?php include "common_leftmenu.php"; ?>
	</div>
        <div class="col-sm-9 col-md-10 main" id="mainContent"><!-- MAIN -->
	    <div class="row top-buffer">
		<h4><i class="fa fa-users" aria-hidden="true"></i> <?php echo _("BOTs"); ?></h4>
		<table class="table table-hover">
		    <thead>
			<tr>
			    <th></th>
			    <th><?php echo _("ID"); ?></th>
			    <th><?php echo _("Name"); ?></th>
			    <th><?php echo _("Last publish"); ?></th>
			    <th><?php echo _("Added on"); ?></th>
			    <th></th>
			</tr>
		    </thead>
		    <tbody>
<?php
		$result = doQuery("SELECT ID FROM Bots WHERE userId=:userId;",array(":userId" => $myUser->ID));
		if($result) {
		    while($row = $result->fetch(PDO::FETCH_ASSOC)) {
			$tmpBot = new Bot($row["ID"]);

			echo "<tr>
			    <td>";
			if($tmpBot->errorCounter > 0) {
			    echo "<i class=\"fa fa-exclamation-triangle text-error\" aria-hidden=\"true\" alt=\"Warning: ".$tmpBot->lastError."\"></i>";
			} else {
			    echo "<i class=\"fa fa-check-circle-o text-success\" aria-hidden=\"true\" alt=\"Online\"></i>";
			}
			echo "</td>
			    <td>$tmpBot->ID</td>
			    <td>$tmpBot->Name</td>
			    <td>".$tmpBot->lastPublish->format('H:i:s m-d-Y')."</td>
			    <td>".$tmpBot->addDate->format('H:i:s m-d-Y')."</td>
			    <td><a href=\"/queue/$tmpBot->ID\" title=\""._("Show BOT $tmpBot->ID queue")."\">
				<i class=\"fa fa-database\" aria-hidden=\"true\"></i>
			    </a><a class=\"ajaxDialog\" title=\""._("Edit BOT $tmpBot->ID")."\" href=\"/bot/$tmpBot->ID\">
				<i class=\"fa fa-pencil-square-o\" aria-hidden=\"true\"></i>
			    </a><a class=\"ajaxDialog\" title=\""._("Re-join BOT $tmpBot->ID")."\" href=\"/webhook.php?key=$tmpBot->ID\">
				<i class=\"fa fa-link\" aria-hidden=\"true\"></i>
			    </a></td>
			</tr>";
		    }
		}
?>	
		</tbody></table>
		<div class="btn-group" role="group">
		    <a class="ajaxDialog btn btn-primary" title="<?php echo _("New BOT"); ?>" href="/bot/"><i class="fa fa-plus" aria-hidden="true"></i> <?php echo _("New BOT"); ?></a>
		</div>
		<hr/>
	    </div>
	</div><!-- /MAIN -->
    </div>
</div><!-- /CONTAINER -->
<?php

include __DIR__."/common_footer.php";

?>
