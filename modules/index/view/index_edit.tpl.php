<div class="box">
<div class="box">
<h3>Alter index:</h3>
<div class="addField">
<?php echo l('index/refresh/' . $vars['name'], '<span class="button">Refresh index</span>'); ?>
</div>

<div class="addField">
<?php echo l('index/gateway_snapshot/' . $vars['name'], '<span class="button">Gateway snapshot</span>'); ?>
</div>

<div class="addField">
<?php echo l('index/flush/' . $vars['name'], '<span class="button">Flush index</span>'); ?>
</div>

<div class="addField">
<?php 
if($vars['state'] == 'close')
{
	echo l('index/open/' . $vars['name'], '<span class="button">Open index</span>'); 
}
else
{
	echo l('index/close/' . $vars['name'], '<span class="button">Close index</span>'); 
}
?>

</div>

<div class="addField">
<?php echo l('index/delete/' . $vars['name'], '<span class="button">Delete index</span>'); ?>
</div>
<br>
<h3>Aliases:</h3>
<ul>
<?php
foreach($vars['aliases'] as $aliasname) {
?>
	<div class="alias">
	<li><?php echo $aliasname; ?> [<?php echo l('index/delete_alias/' . $vars['name'] . '/' . $aliasname, 'delete'); ?>]</li>
	</div>
<?php
}
?>
</ul>
<div class="addField">
<?php echo l('index/create_alias/' . $vars['name'], '<span class="button">Add alias</span>'); ?>
</div>

</div>



<div class="box">
<h3>Document types:</h3>
<ul>
<?php
foreach($vars['mapping_types'] as $type)
{
	echo '<li>' . l('mapping/edit/' . $vars['name'] . '/' . $type, $type) . '</li>';
}
?>
</ul>

<div class="addField">
<?php echo l('index/create_document_type/' . $vars['name'], '<span class="button">Add document type</span>'); ?>
</div>
<br>
<h3>Analyzers:</h3>
<ul>
<?php
foreach($vars['analyzers'] as $analyzer)
{
	echo '<li>' . l('mapping/view_analyzer/' . $vars['name'] . '/' . $analyzer, $analyzer) . '</li>';
}
?>
</ul>
<div class="addField">
<?php echo l('mapping/create_analyzer/' . $vars['name'], '<span class="button">Add analyzer</span>'); ?>
</div>
<br>
<h3>Export:</h3>
<div class="addField">
<?php echo l('index/export/' . $vars['name'], '<span class="button">Export index structure</span>'); ?>
</div>

</div>


<div class="box">
<h3>Data management:</h3>

<div class="addField">
<?php
if($vars['state'] == 'open')
{ 
	echo l('document/search_documents/' . $vars['name'], '<span class="button ' . $vars['state'] . '">Edit documents</span>');
}
else 
{
	echo '<span class="button close">Edit documents</span>';
}
 ?>
</div>

<div class="addField">
<?php 
if($vars['state'] == 'open')
{
	echo l('query/query_builder/' . $vars['name'], '<span class="button ' . $vars['state'] . '">Searchquery builder</span>'); 
}
else 
{
	echo '<span class="button close">Searchquery builder</span>';
}
?>
</div>
<br>
<h3>Add data:</h3>
<ul>
<?php
foreach($vars['mapping_types'] as $type)
{
?>
<li>
<?php 
if($vars['state'] == 'open')
{
	echo l('document/create_document/' . $vars['name'] . '/' . $type, $type); 
}
else 
{
	echo $type;
}
?>
</li>
<?php
}
?>
</ul>


</div>

</div>