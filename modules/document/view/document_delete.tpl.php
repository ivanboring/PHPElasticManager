<h2>Are you sure that you want to delete <?php echo $vars['name']; ?></h2>

<div class="addField">
<?php echo l('document/delete_document_confirm/' . $vars['index'] . '/' . $vars['document_type'] . '/' . $vars['name'], '<span class="button">Delete</span>'); ?>
</div>

<div class="addField">
<?php echo l('document/edit_document/' . $vars['index'] . '/' . $vars['document_type'] . '/' . $vars['name'], '<span class="button">Cancel</span>'); ?>
</div>