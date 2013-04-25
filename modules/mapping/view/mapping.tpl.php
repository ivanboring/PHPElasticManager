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
    <?php echo $vars['structure']; ?>
    </ul>
</div>
