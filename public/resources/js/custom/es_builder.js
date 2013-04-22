/*
 * This is complicated shit for a jQuery/Javascript newbie :)
 */

var dimmed = false;

$(document).ready(function(){
	$('.queryBuilder_plus').live('click', function()
	{
		bindPlus($(this), true);
	});

	$('.queryBuilder_plusMultiple').live('click', function()
	{
		bindPlus($(this), false);
	});	

	$('.queryBuilder_plusMultipleObject').live('click', function()
	{
		bindPlus($(this), false);
	});	
	
	$('.queryBuilder_minus').live('click', function()
	{
		bindMinus($(this));
	});
	
	$('#button').live('click', function()
	{
		calculateObject('queryBuilder');		
	});
	
	$('#download_elastica').live('click', function() {
		donwloadElastica('queryBuilder');
	})
	
	$('#download_emq').live('click', function() {
		downloadEmq('queryBuilder');
	});
});

function downloadEmq(root)
{
	var url = location.search;
	var data = new Object();
	var query = $('#json_code pre').html();
	index = url.split('/');
	query = query.replace(/[ \t\r]+/g,"");
	data['path'] = index[2] + '/_search';
	data['method'] = 'POST';
	data['query'] = query;
	var url = '?q=query/save';
	var form = $('<form action="' + url + '" method="post"><input type="hidden" name="data" value=\'' + JSON.stringify(data) + '\'></form>');
	$('body').append(form);
	$(form).submit();		
}

function donwloadElastica(root)
{
	var outputArray = new Array();
	that = $('#' + root);

	$(that).children('.queryBuilder_base').each(function() {
		var outputString = '';
		var name = $(this).attr('name');
		
		if(name != undefined)
		{
			outputString += name;
		}
		
		$(this).children(':input').each(function() {
			
			name = $(this).attr('name');
			if(!name)
			{
				outputString += $(this).val();
			}
			else if(name == 'setval')
			{
				outputString += '=' + $(this).val();
			}
		});
		
				
		var subs = calculateSubObject(this);
		if(subs == undefined || subs.length != 0) 
		{
			for(var i = 0; i < subs.length; i++)
			{
				var newString = outputString + subs[i];
				outputArray.push(newString);
			}	
		}
		else
		{
			outputArray.push(outputString)
		}
	});
	
	var url = '?q=elastica/create_query_file';
	var form = $('<form action="' + url + '" method="post"><input type="hidden" name="data" value=\'' + JSON.stringify(outputArray) + '\'></form>');
	$('body').append(form);
	$(form).submit();
}

function calculateObject(root)
{
	var outputArray = new Array();
	that = $('#' + root);

	$(that).children('.queryBuilder_base').each(function() {
		var outputString = '';
		var name = $(this).attr('name');
		
		if(name != undefined)
		{
			outputString += name;
		}
		
		$(this).children(':input').each(function() {
			
			name = $(this).attr('name');
			if(!name)
			{
				outputString += $(this).val();
			}
			else if(name == 'setval')
			{
				outputString += '=' + $(this).val();
			}
		});
		
				
		var subs = calculateSubObject(this);
		if(subs == undefined || subs.length != 0) 
		{
			for(var i = 0; i < subs.length; i++)
			{
				var newString = outputString + subs[i];
				outputArray.push(newString);
			}	
		}
		else
		{
			outputArray.push(outputString)
		}
	});
	
	var sendValue = Object;
	sendValue['value'] = JSON.stringify(outputArray);
	
	$.ajax({
		url: '?q=query/search_json',
		type: 'POST',
		data: sendValue,
		dataType: 'json',
		success: function(data) {
			$('#request').html('Request [Download <a id="download_elastica" href=\'#\'>Elastica Class</a>, <a id="download_emq" href=\'#\'>EMQ</a>]');
			$('#json_code pre').html(JSON.stringify(data['jsonarray'], "<br>", "\t"));
			$('#queryBuilder_results pre').html(JSON.stringify(data['result'], "<br>", "\t"));
		}
	});
}

function calculateSubObject(that)
{
	var outputArray = new Array();

	var arraynr = 0;
	$(that).children('.queryBuilder_base').each(function() {
		var outputString = '';
		var name = $(this).attr('name');
		var breakpoint = ';';
		
		if($(this).parent().children('.queryBuilder_plusMultiple').length > 0)
		{
			breakpoint = ';[]' + arraynr + '_';
			arraynr++;
			console.log($(this).parent().attr('name'));
		}
		
		if(name != undefined)
		{
			outputString += breakpoint + name;
		}
		
		$(this).children(':input').each(function() {
			
			name = $(this).attr('name');
			if(!name)
			{
				outputString += breakpoint + $(this).val();
			}
			else if(name == 'setval')
			{
				if(breakpoint == ';')
				{
					breakpoint = '';
				}
				outputString += breakpoint + '=' + $(this).val();
			}
		});

				
		var subs = calculateSubObject(this);
		if(subs == undefined || subs.length != 0) 
		{
			for(var i = 0; i < subs.length; i++)
			{
				var newString = outputString + subs[i];
				outputArray.push(newString);
			}	
		}
		else
		{
			outputArray.push(outputString)
		}
		
	});

	return outputArray;
}


function bindMinus(nestedObject)
{
	if(!dimmed)
	{	
		$(nestedObject).nextAll().remove();
		nestedObject.parent().parent().find('.queryBuilder_plus').show();
		nestedObject.parent().remove();
	}
}

function bindSelect(select, addName)
{
	output = getSelectOptions(select.attr('id'), select.val());
	if(addName)
	{
		var newname = select.val();
		var parts = newname.split('_');
		parts.splice(0, 1);
		var newname = parts.join('_');
		$(select).parent().attr('name', newname);	
	}
	$(select).parent().find('.queryBuilder_minus').remove();
	$(select).parent().append(output);
	$(select).remove();
	dimmed = false;
}

function getSelectOptions(type, value)
{
	output = '';
	if(type.substr(-7) == '_select')
	{
		output += getQuerySelect(value);
	}
	return output;
}

function getFields()
{
	var output = '';
	for(var i = 0; i < fields.length; i++)
	{
		output += '<option value="' + fields[i] + '">' + fields[i];
	}
	return output;
}

function getTypes()
{
	var output = '';
	for(var i = 0; i < types.length; i++)
	{
		output += '<option value="' + types[i] + '">' + types[i];
	}
	return output;
}

function getIndex()
{
	var output = '';
	for(var i = 0; i < indexes.length; i++)
	{
		output += '<option value="' + indexes[i] + '">' + indexes[i];
	}
	return output;
}

function getAnalyzers()
{
	var output = '';
	for(var key in analyzers)
	{
		output += '<option value="' + key + '">' + analyzers[key];
	}
	return output;
}

