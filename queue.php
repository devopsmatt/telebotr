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


$gbTailCode = "<script type=\"text/javascript\">
$(function (){
    $('a.ajaxCustomCall').click(function() {
	var url = this.href;
	var container = this;

	var tr = $(this).closest('tr');

	$.ajax({
    	    url: url,
    	    dataType: 'html',
    	    success: function(data) {
		tr.find('td').fadeOut(1000,function(){ 
		    tr.remove();
    		}); 
    	    },
	    error: function(XMLHttpRequest, textStatus, errorThrown) {
		$(container).html('ERROR');
	    }
	});
	return false;
    });
});
</script>";

include __DIR__."/common.inc.php";

if(!$mySession->isLogged()) {
    header("Location: /");
    exit();
}

include __DIR__."/common_header.php";

?>
<div class="container-fluid"><!-- CONTAINER -->
    <div class="row">
	<div class="col-sm-3 col-md-2 sidebar">
	    <?php include "common_leftmenu.php"; ?>
	</div>
        <div class="col-sm-9 col-md-10 main" id="mainContent"><!-- MAIN -->
	    <div class="row">
<?php
	    if(empty($_GET["id"])) {
?>
		<h4><i class="fa fa-stack-exchange" aria-hidden="true"></i> <?php echo _("Queues"); ?></h4>
		<table class="table table-hover table-striped">
		    <thead>
			<tr>
			    <th><?php echo _("Name"); ?></th>
			    <th><?php echo _("Type"); ?></th>
			    <th><?php echo _("Waiting posts"); ?></th>
			    <th><?php echo _("Total posts"); ?></th>
			    <th><?php echo _("Last publish"); ?></th>
			</tr>
		    </thead>
		    <tbody>
<?php
		$result = doQuery("SELECT ID FROM Chats WHERE userId=:user_id;",array(":user_id" => $mySession->userId));
		if($result) {
		    while($row = $result->fetch(PDO::FETCH_ASSOC)) {
			$tmpChannel = new Channel($row["ID"]);

			$tmpBot = new Bot($tmpChannel->botId);

			$waitingPosts = $row["waitingPosts"];
			$totalPosts = $row["totalPosts"];

			echo "<tr class=\"".($tmpChannel->isEnable?"":"table-danger")."\" data-href=\"/queue/$bot->ID\">
			    <td>$tmpChannel->Title</td>
			    <td>$tmpChannel->Type</td>
			    <td>$waitingPosts</td>
			    <td>$totalPosts</td>
			    <td>".(empty($tmpBot->lastPublish) ? "never" : $tmpBot->lastPublish->format('H:m:s m-d-Y'))."</td>
			</tr>";
		    }
		} else {
?>
	    	    <div class="alert alert-warning" role="alert">
		        <strong>Ooops !</strong> We need at least <a href="/configs">a Bot or a Channel configured</a> before...
		    </div>
<?php
	        }
?>	
		</tbody></table>
<?php
	    } else {
		$bot = new Bot(intval($_GET["id"]));
		if(!empty($bot)) {
?>
		    <h4><i class="fa fa-database" aria-hidden="true"></i> <?php printf(_("Posts queue for BOT %s"),$bot->Name); ?></h4>
		    <table class="table table-hover">
			<thead>
			    <tr>
				<th><?php echo _("Title"); ?></th>
				<th><?php echo _("URL"); ?></th>
				<th><?php echo _("Views"); ?></th>
				<th><?php echo _("Add date"); ?></th>
				<th><?php echo _("Publish date"); ?></th>
				<th></th>
			    </tr>
			</thead>
			<tbody>
<?php
		    $result = doQuery("SELECT ID FROM Posts WHERE userId=:user_id AND botId=:bot_id ORDER BY addDate DESC LIMIT 20;",array(":user_id" => $mySession->userId, ":bot_id" => $bot->ID));
	    	    if($result) {
			while($row = $result->fetch(PDO::FETCH_ASSOC)) {
			    $post = new Post($row["ID"]);
			    echo "<tr class=\"".($post->isPublished ? "":"table-info")."\" id=\"tr-post-$post->ID \">
			    <td>$post->Title</td>
			    <td><a href='$post->URL' target='new'>$post->URL</a></td>
			    <td>$post->Views</td>
			    <td>".$post->addDate->format('H:m:s d-m-Y')."</td>
			    <td>".(is_null($post->publishDate) ? "not yet":$post->publishDate->format('H:m:s d-m-Y'))."</td>
			    <td>";
			    if(!$post->isPublished) {
				echo "<a class=\"ajaxCall\" title=\""._("Toggle this post")."\" href=\"/ajaxCb.php?action=togglePost&ID=$post->ID\">
				    <i class=\"fa fa-".($post->isActive ? "pause":"play")."\" aria-hidden=\"true\"></i>
				</a><a class=\"ajaxCustomCall\" title=\""._("Delete this post")."\" href=\"/ajaxCb.php?action=deletePost&ID=$post->ID\">
				    <i class=\"fa fa-trash\" aria-hidden=\"true\"></i>
				</a>";
			    } 
			    echo "</td>
			    </tr>";
			}
		    }
?>
		    </tbody>
		</table>
		<hr>
		<div class="btn-group">
		    <a href="/queue" class="btn btn-primary" aria-current="page"><?php echo _("Back"); ?></a>
		</div>
<?php
	    }
	}
?>
	    </div>
	</div><!-- /MAIN -->
    </div>
</div><!-- /CONTAINER -->
<?php

include __DIR__."/common_footer.php";

?>
