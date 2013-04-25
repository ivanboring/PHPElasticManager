var counter = new Object;
	
$(document).ready(function(){
	var url = location.search;
	index = url.split('/');
	
	// Match all link elements with href attributes within the content div
	$('.title').bind('click', function() {
		$('#' + ($(this).attr('name') + '_document')).toggle();
	});
	
	$('.formbutton #delete').bind('click', function() {
		window.location.href='?q=document/delete_document/' + index[2] + '/' + index[3] + '/' + index[4];
	});
	
	$('.form-nested .button').live('click', function() {
		var id = $(this).parent().attr('id');
		if(typeof counter[id] == 'undefined') counter[id] = 0;
		else counter[id] = counter[id] + 1;
		var protothis = this;
		$.getJSON('?q=document/form_nested/' + index[2] + '/' + index[3] + '/' + id.replace(/_-_/g, '.') + '/' + counter[id], function(data) {
			$(protothis).parent().children('.data').append(data.form);
			//$('#' + id + ' .data').html($('#' + id + ' .data').html() + data.form);
		});
	});
	
	$('.form-nested .close-nested').live('click', function() {
		$(this).parent().parent().remove();
	});
});