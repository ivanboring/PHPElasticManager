<h2>Are you sure that you want to delete <?php echo $vars['name']; ?></h2>

<div class="addField">
<?php echo l('index/delete_confirm/' . $vars['name'], '<span class="button">Delete</span>'); ?>
</div>

<div class="addField">
<?php echo l('index/edit/' . $vars['name'], '<span class="button">Cancel</span>'); ?>
</div>
