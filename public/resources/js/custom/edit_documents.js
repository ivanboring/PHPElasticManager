$(document).ready(function(){
	// Match all link elements with href attributes within the content div
	$('.title').bind('click', function() {
		$('#' + ($(this).attr('name') + '_document')).toggle();
	});
	
	$('.formbutton #delete').bind('click', function() {
		var url = location.search;
		index = url.split('/');
		window.location.href='?q=document/delete_document/' + index[2] + '/' + index[3] + '/' + index[4];
	});
});