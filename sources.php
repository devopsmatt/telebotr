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
		<h4><i class="fa fa-cubes" aria-hidden="true"></i> <?php echo _("Sources"); ?></h4>
		<table class="table table-hover">
		    <thead>
			<tr>
			    <th><?php echo _("ID"); ?></th>
			    <th><?php echo _("Name"); ?></th>
			    <th><?php echo _("Description"); ?></th>
			    <th><?php echo _("URL"); ?></th>
			    <th><?php echo _("Added on"); ?></th>
			    <th></th>
			</tr>
		    </thead>
		    <tbody>
<?php
		$result = doQuery("SELECT ID FROM Sources WHERE userId=:userId;", array(":userId" => $myUser->ID));
		if($result) {
		    while($row = $result->fetch(PDO::FETCH_ASSOC)) {
			$source = new Source($row["ID"]);
			echo "<tr>
			    <td>";
			if($source->botId == 0) {
			    echo "<i class=\"fa fa-fa-exclamation-triangle text-error\" aria-hidden=\"true\" alt=\"Warning: no BOT selected !\"></i>";
			}
			echo " $source->ID</td>
			    <td>$source->Name</td>
			    <td>$source->Description</td>
			    <td><a href='$source->URL' target='_new'>$source->URL</a></td>
			    <td>".$source->addDate->format('m-d-Y h:m:s')."</td>
			    <td><a class=\"ajaxDialog\" title=\""._("Edit source $source->Name")."\" href=\"/ajaxCb.php?action=editSource&ID=$source->ID\">
				<i class=\"fa fa-pencil-square-o\" aria-hidden=\"true\"></i>
			    </a><a class=\"ajaxDialog\" title=\""._("Source $source->Name operation log")."\" href=\"/ajaxCb.php?action=checkSource&ID=$source->ID\">
				<i class=\"fa fa-heartbeat\" aria-hidden=\"true\"></i>
			    </a><a class=\"ajaxDialog\" title=\""._("Fetch source $source->Name")."\" href=\"/ajaxCb.php?action=fetchSource&ID=$source->ID\">
				<i class=\"fa fa-cogs\" aria-hidden=\"true\"></i>
			    </a></td>
			</tr>";
		    }
		}
?>	
		</tbody></table>
		<div class="btn-group" role="group">
		    <a class="ajaxDialog btn btn-primary" title="<?php echo _("New RSS source"); ?>" href="/ajaxCb.php?action=editSource"><i class="fa fa-plus" aria-hidden="true"></i> <?php echo _("New RSS source"); ?></a>
		</div>
		<br/><br/>
		<hr/>
	    </div>
	</div><!-- /MAIN -->
    </div>
</div><!-- /CONTAINER -->
<?php

include __DIR__."/common_footer.php";

?>
