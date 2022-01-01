// === DOCUMENT READY
$(document).ready(function() {
    $(".table tr[data-href]").click(function(){
	var url = $(this).attr("data-href");
	var dataId = $(this).attr("data-id");

	$("#popup-dialog").modal("show");

	$.ajax({
	    type: "POST",
	    contentType: "application/json; charset=utf-8",
	    url: url,
	    data: {id:dataId},
	    success: function (data) {
		console.log(data);
    		$("#modal-dialog-title").html(data.title);
    	    	$("#modal-dialog-body").html(data.body);
	    },error: function (xhr, textStatus, error) {
		var response = JSON.parse(xhr.responseText);
		console.log(response.message);
	    }
	});
    });

    $(".ajaxCall").click(function() {
	var action = $(this).attr("data-href");
	var dataId = $(this).attr("data-id");
	$.post({
    	    url: "/ajaxCb/"+action,
	    data: {id:dataId},
	    success: function(data) {
		console.log(data);
	    },
	    timeout: 1000
	});
    });


    function doPoll() {
	$.post({
    	    url: "/ajax/doPoll",
	    dataType: "json",
	    success: function (data) {
		new Noty({
		    type: data.level,
		    layout: "topCenter",
		    text: data.notice
		}).show();
	    },
	    timeout: 1000
	}).always(function () {
	    setTimeout(doPoll, 5000)
	})
    }
    doPoll()
});
