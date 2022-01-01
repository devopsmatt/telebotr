<?php

include __DIR__.'/../common.inc.php';

if((!$mySession->isLogged())||(!$myUser->isAdmin())) {
    $mySession->sendMessage("Forbidden","error");
    header('Location: /');
    exit();
}

include __DIR__.'/../common_header.php';

?>
<div class="container-fluid"><!-- CONTAINER -->
    <div class="row">
	<div class="col-sm-3 col-md-2 sidebar">
	    <?php include __DIR__."/../common_leftmenu.php"; ?>
	</div>
        <div class="col-sm-9 col-md-10 main" id="mainContent"><!-- MAIN -->
	    <div class="row">
		<h4><i class="fa fa-users" aria-hidden="true"></i> <?php echo _("Users"); ?></h4>
		<table class="table table-hover">
		    <thead>
			<tr>
			    <th><?php echo _("ID"); ?></th>
			    <th><?php echo _("Name"); ?></th>
			    <th><?php echo _("eMail"); ?></th>
			    <th><?php echo _("AVP"); ?></th>
			    <th><?php echo _("Last login"); ?></th>
			    <th><?php echo _("Add Date"); ?></th>
			    <th></th>
			</tr>
		    </thead>
		    <tbody>
<?php
		$result = doQuery("SELECT ID FROM Users;");
		if($result) {
		    while($row = $result->fetch(PDO::FETCH_ASSOC)) {
			$user = new User($row["ID"]);
			$user_adddate = new DateTime($row["addDate"]);
			$user_lastlogin = new DateTime($row["lastLogin"]);
			$user_isenable = $row["isEnable"];
			echo "<tr class='".($user_isenable ? "":"table-active")."'>
			    <td>$user->ID</td>
			    <td>$user->Name</td>
			    <td>$user->Email</td>
			    <td>";
			foreach($user->AVP as $name => $value) {
			    echo "$name => $value<br/>";
			}
			echo "</td>
			    <td>".$user_lastlogin->format('H:m:s d-m-Y')."</td>
			    <td>".$user_adddate->format('H:m:s d-m-Y')."</td>
			    <td>";
			if(!$user_isenable) {
			    echo "<a class=\"ajaxDialog\" title=\""._("Enable account")."\" href=\"/ajaxCb.php?action=userEnable&ID=$user->ID\">
				    <i class=\"fa fa-toggle-on\" aria-hidden=\"true\"></i>
			    	</a>";
			}
			if(!$user->isAdmin()) {
			    echo "<a class=\"ajaxDialog\" title=\""._("Delete account")."\" href=\"/ajaxCb.php?action=userDelete&ID=$user->ID\">
				    <i class=\"fa fa-times\" aria-hidden=\"true\"></i>
				</a>";
			}
			echo "</td>
			</tr>";
		    }
		}
?>	
		</tbody></table>
	    </div>
	</div>
    </div>
</div>
<?php

include __DIR__.'/../common_footer.php';

?>
