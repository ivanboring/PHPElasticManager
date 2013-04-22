<div class="box" id="queryBuilder" style="text-align: left;">
Root <div class="queryBuilder_plusMultiple" name="root">[+]</div>
<div class="queryBuilder_base queryBuilder_nested" name="query">query <div class="queryBuilder_plus" name="query">[+]</div></div>
<div class="queryBuilder_base queryBuilder_nested" name="facets">facets <div class="queryBuilder_plusMultipleObject" name="facets">[+]</div></div>
<div class="queryBuilder_base queryBuilder_nested" name="sort">sort <div class="queryBuilder_plusMultiple" name="sort">[+]</div></div>
<div class="queryBuilder_base queryBuilder_nested" name="from">from: <input name="setval" type="text" value="0"></div>
<div class="queryBuilder_base queryBuilder_nested" name="size">size: <input name="setval" type="text" value="50"></div>
</div>
<input type="button" id="button" style="margin-top: 20px; width:100px; height: 30px;" value="Test">

<h3 id="request">Request</h3>
<div id="json_code" class="box">
	<pre>
		
	</pre>
</div>

<h3>Response</h3>
<div id="queryBuilder_results" class="box">
	<pre>
		
	</pre>
</div>

<script type="text/javascript" src="?q=query/query_builder_js/<?php echo $vars[0]; ?>"></script>

<script>
var fields = <?php echo $vars['fields']; ?>;
var indexes = <?php echo $vars['indexes']; ?>;
var types = <?php echo $vars['types']; ?>;
var analyzers = <?php echo $vars['analyzers']; ?>;
</script>