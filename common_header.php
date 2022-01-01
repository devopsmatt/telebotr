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

include_once "common.inc.php";

include_once "common_cb.php";

?>
<!DOCTYPE html>
<html lang="en_GB" class="h-100">
    <head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<meta name="description" content="Telegram channel publishing platform">
	<meta name="author" content="Michele <o-zone@zerozone.it> Pinassi">
	<meta name="keywords" content="telegram, blog, rss, feed, broadcast, channel, bot">

	<meta property="og:title" content="TeleBotr" />
	<meta property="og:description" content="TeleBotr - Telegram publishing platform" />
	<meta property="og:url" content="" />
	<meta property="og:image" content="" />

	<title>TeleBotr - Telegram publishing platform</title>

	<link href="/css/bootstrap.min.css" rel="stylesheet">

	<link href="/css/glyphicons.css" rel="stylesheet">
	<link href="/css/font-awesome.min.css" rel="stylesheet">

	<link href="/vendor/needim/noty/lib/noty.css" rel="stylesheet">

	<link href="/css/common.css" rel="stylesheet">

<?php
$local_css = '00-'.basename($_SERVER['SCRIPT_FILENAME'],".php").".css";

if(file_exists("./css/".$local_css)) {
    echo "\t<!-- local CSS -->\n\t<link href=\"/css/".$local_css."\" rel=\"stylesheet\">\n";
}
?>
	<link rel="icon" href="/favicon.ico" type="image/x-icon" />
	<link rel="shortcut icon" href="/favicon.ico" type="image/x-icon" />
    </head><body class="d-flex flex-column h-100">
	<header>
	    <nav class="navbar navbar-expand-md navbar-dark bg-dark fixed-top"><!-- NAVBAR -->
		<div class="container-fluid">
		    <a class="navbar-brand" href="/"><img src="/img/logo_rev_48x.png" class="logo" /></a>
		    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarCollapse" aria-controls="navbarCollapse" aria-expanded="false" aria-label="Toggle navigation">
			<span class="navbar-toggler-icon"></span>
		    </button>
		    <div class="collapse navbar-collapse" id="navbarCollapse">
			<ul class="navbar-nav me-auto mb-2 mb-md-0">
			    <li class="nav-item">
				<a class="nav-link active" aria-current="page" href="/home">Home</a>
			    </li>
			</ul>
		    </div>
		    <div class="d-flex text-light">
<?php
if($mySession->isLogged()) {
    echo $myUser->Name;
} else {
    echo "Not logged";
}
?>
		    </div>
		</div>
	    </nav><!-- /NAVBAR -->
	</header>
	<main>
	    <div class="container h-100"><!-- MAIN CONTAINER -->
