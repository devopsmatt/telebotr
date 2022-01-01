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
		<h4><i class="fa fa-cloud" aria-hidden="true"></i> <?php echo _("Channels"); ?></h4>
		<table class="table table-hover">
		    <thead>
			<tr>
			    <th><?php echo _("ID"); ?></th>
			    <th><?php echo _("Type"); ?></th>
			    <th><?php echo _("Bot linked"); ?></th>
			    <th><?php echo _("Added on"); ?></th>
			    <th><?php echo _("Last update"); ?></th>
			    <th></th>
			</tr>
		    </thead>
		    <tbody>
<?php
		$result = doQuery("SELECT t1.ID,t1.botId,t1.Type,t1.addDate,t2.chgDate FROM Chats AS t1 JOIN Bots AS t2 ON t1.botId=t2.ID WHERE t2.userId=:userId;",array(":userId" => $myUser->ID));
		if($result) {
		    while($row = $result->fetch(PDO::FETCH_ASSOC)) {
			$channel_id = $row["ID"];
			$channel_bot_id = $row["botId"];
			$channel_type = $row["Type"];
			$channel_adddate = new DateTime($row["addDate"]);
			$channel_chgdate = new DateTime($row["chgDate"]);

			echo "<tr>
			    <td>$channel_id</td>
			    <td>$channel_type</td>
			    <td>$channel_bot_id</td>
			    <td>".$channel_adddate->format('H:i:s m-d-Y')."</td>
			    <td>".$channel_chgdate->format('H:i:s m-d-Y')."</td>
			    <td>";
			if($channel_type === 'channel') {
			    echo "<a class=\"ajaxDialog\" title=\""._("Edit Channel $channel_id")."\" href=\"/ajaxCb.php?action=editChannel&ID=$channel_id\">
				<i class=\"fa fa-pencil-square-o\" aria-hidden=\"true\"></i>
				</a>
				<a href='tg://resolve?domain=$channel_id'><i class=\"fa fa-paper-plane-o\" aria-hidden=\"true\"></i></a>";
			}
			echo "</td>
			</tr>";
		    }
		}
?>	
		</tbody></table>
		<div class="btn-group" role="group">
		    <a class="ajaxDialog btn btn-primary" title="<?php echo _("Add Telegram Channel"); ?>" href="/ajaxCb.php?action=editChannel"><i class="fa fa-plus" aria-hidden="true"></i> <?php echo _("Add Telegram Channel"); ?></a>
		</div>
		<hr/>
	    </div>
	</div><!-- /MAIN -->
    </div>
</div><!-- /CONTAINER -->
<?php

include __DIR__."/common_footer.php";

?>
