$(document).ready(function()
{
	$('.indexInfo').bind('mouseover', function() {
		$(this).find(' .moreInfo').slideDown();
	});

	$('.indexInfo').bind('mouseleave', function() {
		$(this).find(' .moreInfo').slideUp();
	});
	
	$('#validateQuery').bind('change', function() {
		if($(this).is(':checked')) {
			$.get('?q=query/validation/1', function(data) {});
		}
		else
		{
			$.get('?q=query/validation/0', function(data) {});
		}
	});
});