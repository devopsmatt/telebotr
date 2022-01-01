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
	    </div><!-- /MAIN CONTAINER -->
	    <div class="modal fade" id="popup-dialog" tabindex="-1" role="dialog" aria-labelledby="modal-dialog-title" aria-hidden="true"><!-- MODAL DIALOG -->
		<div class="modal-dialog modal-dialog-centered" role="document">
		    <div class="modal-content">
			<div class="modal-header">
			    <h5 class="modal-title" id="modal-dialog-title"></h5>
			</div>
			<div class="modal-body" id="modal-dialog-body">
			    <center><img src="/img/loader.gif"></center>
			</div>
			<div class="modal-footer">
			    <button type="button" class="btn btn-secondary" onclick="$('#popup-dialog').modal('hide');">Close</button>
			    <button type="button" class="btn btn-primary">Save changes</button>
			</div>
		    </div>
    		</div>
	    </div><!-- /MODAL DIALOG -->
	</main>
	<footer class="footer mt-auto py-3 bg-light">
    	    <div class="container">
	        <div class="row justify-content-md-center">
    	    	    <div class="text-center">
            		<h4>
			    <strong>TeleBotr</strong>
        		</h4>
        		<p>Made with <i class="fa fa-heart fa-fw"></i> in Siena, Tuscany, Italy</p>
            		<hr class="small">
        	    </div>
        	</div>
    	    </div>
	</footer>

	<script src="/js/jquery.min.js"></script>
	<script>window.jQuery</script>
	<script src="/js/bootstrap.bundle.min.js"></script>
	<script src="/vendor/needim/noty/lib/noty.min.js"></script>
	<script src="/js/common.js"></script>
<?php
$local_js = '00-'.basename($_SERVER['SCRIPT_FILENAME'],".php").".js";

if(file_exists("./js/".$local_js)) {
    echo "\t<!-- local JS -->\n\t<script src=\"/js/".$local_js."\"></script>\n";
}
?>
    </body>
</html>
