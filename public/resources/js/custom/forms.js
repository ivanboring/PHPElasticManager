$(document).ready(function(){
	// Match all link elements with href attributes within the content div
	$('.tooltip').qtip(
	{
		content: {
			text: false
		},
		position: {
			corner: {
				target: 'topLeft',
				tooltip: 'bottomRight'
			}
		}
	});
});