$(document).ready(function(){
	$('#type').change(function() {
		field_show($('#type').val());
	});
});

function field_show(type)
{
	var show = new Array(
		'.input-checkbox-store',
		'.input-checkbox-include_in_all',
		'#na',
		'.input-term_vector',
		'.input-boost',
		'.input-ignore_above',
		'.input-position_offset_gap',
		'.input-null_value',
		'.input-omit_norm',
		'.input-index_options',
		'.input-precision_step',
		'.input-ignore_malformed',
		'.input-format',
		'.input-include_in_parent',
		'.input-include_in_root',
		'.input-path',
		'.input-lat_lon',
		'.input-geohash',
		'.input-geohash_precision',
		'.input-tree',
		'.input-precision',
		'.input-tree_levels',
		'.input-distance_error_pct'
	);
	
	for(var i = 0; i < show.length; i++)
	{
		$(show[i]).hide();
	}
	
	switch(type)
	{
		case 'string':
			var show = new Array(
				'.input-checkbox-store',
				'.input-checkbox-include_in_all',
				'#na',
				'.input-term_vector',
				'.input-boost',
				'.input-ignore_above',
				'.input-position_offset_gap',
				'.input-null_value',
				'.input-omit_norm',
				'.input-index_options'
			);		
			break;
		case 'integer':
		case 'long':
		case 'short':
		case 'byte':
		case 'float':
		case 'double':
			var show = new Array(
				'.input-checkbox-store',
				'.input-checkbox-include_in_all',
				'#na',
				'.input-boost',
				'.input-null_value',
				'.input-precision_step',
				'.input-ignore_malformed'
			);		
			break;
		case 'date':
			var show = new Array(
				'.input-checkbox-store',
				'.input-checkbox-include_in_all',
				'#na',
				'.input-precision_step',
				'.input-boost',
				'.input-ignore_malformed',
				'.input-null_value',
				'.input-format'
			);			
			break;
		case 'boolean':
			var show = new Array(
				'.input-checkbox-store',
				'.input-checkbox-include_in_all',
				'#na',
				'.input-boost',
				'.input-null_value'
			);		
			break;
		case 'binary':
			var show = new Array();		
			break;
		case 'nested':
			var show = new Array(
				'.input-include_in_parent',
				'.input-include_in_root'
			);
			break;
		case 'object':
			var show = new Array(
				'.input-path',
				'.input-checkbox-include_in_all'
			);
			break;
		case 'ip':
			var show = new Array(
				'.input-checkbox-store',
				'.input-checkbox-include_in_all',
				'#na',
				'.input-precision_step',
				'.input-boost',
				'.input-ignore_malformed',
				'.input-null_value'
			);
			break;
		case 'geo_point':
			var show = new Array(
				'.input-lat_lon',
				'.input-geohash',
				'.input-geohash_precision'
			);	
			break;
		case 'geo_shape':
			var show = new Array(
				'.input-tree',
				'.input-precision',
				'.input-tree_levels',
				'.input-distance_error_pct'
			);
			break;
		case 'attachment':
			var show = new Array();		
			break;	
		default:
			var show = new Array();
	}
	
	for(var i = 0; i < show.length; i++)
	{
		$(show[i]).show();
	}
}
