$(document).ready(function(){
	// Match all link elements with href attributes within the content div
	$('.formbutton #save').bind('click', function() {
		var data = new Object();
		data['path'] = $('#path').val();
		data['method'] = $('#method').val();
		data['query'] = $('#query').val();
		var url = '?q=query/save';
		var form = $('<form action="' + url + '" method="post"><input type="hidden" name="data" value=\'' + JSON.stringify(data) + '\'></form>');
		$('body').append(form);
		$(form).submit();			
	});
	$('.formbutton #load').bind('click', function() {
		$('#file').trigger('click');
	});
	
	$('#file').change(function() {
		$('#filesubmit').trigger('click');
	});
});