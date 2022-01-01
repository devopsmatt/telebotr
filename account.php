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
?>
<div class="container-fluid"><!-- CONTAINER -->
    <div class="row">
	<div class="col-sm-3 col-md-2 sidebar">
	    <?php include "common_leftmenu.php"; ?>
	</div>
        <div class="col-sm-9 col-md-10 main" id="mainContent"><!-- MAIN -->
	    <h1><?php echo _("My Account"); ?></h1>
	    <form method="POST">
		<input type="hidden" name="nonde" value="<?php echo $mySession->getNonce(); ?>">
		<div class="mb-3 row">
		    <label for="userEmail" class="col-sm-2 col-form-label"><?php echo _("Email"); ?></label>
		    <div class="col-sm-10">
    			<input type="text" readonly class="form-control-plaintext" id="userEmail" value="<?php echo $myUser->Email; ?>">
		    </div>
		</div><div class="mb-3 row">
		    <label for="userName" class="col-sm-2 col-form-label"><?php echo _("Name"); ?></label>
		    <div class="col-sm-10">
			<input type="text" class="form-control" id="userName" name="userName" value="<?php echo $myUser->Name; ?>">
		    </div>
		</div><div class="mb-3 row">
		    <label for="userPassword" class="col-sm-2 col-form-label"><?php echo _("Password"); ?></label>
		    <div class="col-sm-10">
			<input type="password" class="form-control" id="userPassword">
		    </div><div class="form-text">
			<?php echo _("Leave blank if you don't want to change your password"); ?>
		    </div>
		</div>
		<input type="submit" value="<?php echo _("Submit"); ?>">
	    </form>
	</div><!-- /MAIN -->
    </div>
</div><!-- /CONTAINER -->
<?php
} else {
    header("Location: /");
}

include "common_footer.php";

?>
