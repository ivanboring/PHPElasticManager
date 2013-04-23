$(document).ready(function(){
	var checkboxes = new Array(
			'tokenfilter_word_delimiter_check', 
			'tokenfilter_snowball_check', 
			'tokenfilter_length_check',
			'tokenfilter_ngram_check',
			'tokenfilter_edgengram_check', 
			'tokenfilter_shingle_check',
			'tokenfilter_stop_check', 
			'tokenfilter_stemmer_check',
			'tokenfilter_stemmer_override_check',
			'tokenfilter_keyword_marker_check', 
			'tokenfilter_phonetic_check', 
			'tokenfilter_synonym_check',
			'tokenfilter_compund_word_check',
			'tokenfilter_elision_check',
			'tokenfilter_truncate_check',
			'tokenfilter_unique_check',
			'tokenfilter_pattern_replace_check',
			'not_analyzed_check',
			'searchable'
		);
	
	for (var i=0;i<checkboxes.length;i++)
	{
		checkuncheckhide(checkboxes[i]);
		checkuncheck(checkboxes[i]);
	}
});

function checkuncheckhide(name)
{
	if($('#' + name).is(':checked') != true)
	{
		$('#' + name).parent().parent().find('div:not(:first-child)').hide();	
	}
}

function checkuncheck(name)
{
	$('#' + name).bind('change', function() {
		if($(this).is(':checked')) {
			var bestclass = $(this).parent().attr('class').split(' ');
			$('#' + name).parent().parent().find('div:not(.' + bestclass[2] + ')').slideDown();
		}
		else
		{
			var bestclass = $(this).parent().attr('class').split(' ');
			$('#' + name).parent().parent().find('div:not(.' + bestclass[2] + ')').slideUp();
		}
	});

	
}