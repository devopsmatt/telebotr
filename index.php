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
// LOGGED USERS -> REDIRECT TO HOME
    header('Location: /home');
} else { 
// NON LOGGED USERS -> SHOW LOGIN FORM
?>
<div class="row">
    <div class="col-sm-9 col-md-7 col-lg-5 mx-auto">
	<div class="card card-signin my-5">
	    <div class="card-body">
		<h5 class="card-title text-center">Log In</h5>
		<form class="form-signin" method="POST">
		    <input type="hidden" name="action" value="userLogin">
		    <input type="hidden" name="nonce" value="<?php echo $mySession->getNonce(); ?>">
		    <div class="form-label-group">
			<input type="email" id="userEmail" name="userEmail" class="form-control" placeholder="Email address" required autofocus>
			<label for="userEmail">Email address</label>
		    </div>
		    <div class="form-label-group">
			<input type="password" id="userPassword" name="userPassword" class="form-control" placeholder="Password" required>
			<label for="userPassword">Password</label>
		    </div>
		    <button class="btn btn-lg btn-primary btn-block text-uppercase" type="submit">Log in</button>
		</form>
	    </div>
	</div>
    </div>
</div>
<?php
}

include "common_footer.php";

?>