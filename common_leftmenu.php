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

?>
<div class="card">
    <div class="card-header">
	Menù
    </div>
    <div class="list-group list-group-flush">
	<a href="/home" class="list-group-item">
    	    <i class="fa fa-home"></i> <?php echo _("Overview"); ?>
	</a>
	<a href="/reports" class="list-group-item">
    	    <i class="fa fa-pie-chart"></i> <?php echo _("Reports"); ?>
	</a>
	<a href="/queue" class="list-group-item">
    	    <i class="fa fa-stack-exchange"></i> <?php echo _("Queues"); ?>
	</a>
	<a href="/sources" class="list-group-item">
    	    <i class="fa fa-rss"></i> <?php echo _("Sources"); ?>
	</a>
	<a href="/bots" class="list-group-item">
    	    <i class="fa fa-users"></i> <?php echo _("Bots"); ?>
	</a>
	<a href="/channels" class="list-group-item">
    	    <i class="fa fa-cubes"></i> <?php echo _("Channels"); ?>
	</a>
<?php
if($myUser->isAdmin()) {
/* Administrator-only menu */
?>
	<a href="/admin/users" class="list-group-item">
    	    <i class="fa fa-users"></i> <?php echo _("Users"); ?>
	</a>
<?php
}
?>
	<a href="/account" class="list-group-item">
    	    <i class="fa fa-user"></i> <?php echo _("My account"); ?>
	</a>
	<a href="/logout" class="list-group-item">
    	    <i class="fa fa-sign-out"></i> <?php echo _("Logout"); ?>
	</a>
    </div>
</div>