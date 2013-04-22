<div class="box">
	<h3>Choose file type to export as</h3>
	<div class="addField">
	<?php echo l('elastica/export/' . $vars[0], '<span class="button">Save as Elastica class</span>'); ?>
	</div>
	<div class="addField">
	<?php echo l('index/export_bash/' . $vars[0], '<span class="button">Save as bash file (requires curl)</span>'); ?>
	</div>
	<div class="addField">
	<?php echo l('index/export_emq/' . $vars[0], '<span class="button">Save as PHPElasticManager query file</span>'); ?>
	</div>
</div>
