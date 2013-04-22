<div class="box arrows">
	<h3>Operations:</h3>
	<div class="addField">
	<?php echo l('mapping/create_field/' . $vars['name'] . '/' . $vars['document_type'], '<span class="button">Add field</span>'); ?>
	</div>
	<div class="addField">	
	<?php echo l('elastica/document/' . $vars['name'] . '/' . $vars['document_type'], '<span class="button">Download Elastica classfile</span>'); ?>
	</div><br>
	<h3>Field structure:</h3>
	<ul>
	<?php foreach($vars['properties'] as $key => $value) { ?>
		<li><strong><?php echo $key; ?></strong>
		<ul>
		<?php foreach($value as $formkey => $formvalue) { ?>
			<li><strong><?php echo $formkey; ?>:</strong> <?php echo $formvalue; ?></li>
		<?php } ?>
		</ul>			
		</li>
	<?php } ?>
	</ul>
</div>
