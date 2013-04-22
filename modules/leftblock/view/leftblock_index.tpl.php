<div id="leftblock-index-<?php echo $vars['name'] ?>" class="indexInfo <?php $vars['data']['state']; ?>">
<h3><?php echo l('index/edit/' . $vars['name'], $vars['name']); ?></h3>
<div class="up">
	<div class="green_small small">
	<?php echo $vars['up']['green']; ?>
	</div>
	<div class="yellow_small small">
	<?php echo $vars['up']['yellow']; ?>	
	</div>
	<div class="red_small small">
	<?php echo $vars['up']['red']; ?>
	</div>
</div>
<br>

<div class="moreInfo">
	<strong>Alter:</strong><br>
	<?php echo l('index/refresh/' . $vars['name'], 'Refresh'); ?><br>
	<?php echo l('index/gateway_snapshot/' . $vars['name'], 'Gateway snapshot'); ?><br>
	<?php echo l('index/flush/' . $vars['name'], 'Flush'); ?><br>
	<?php 
	if($vars['state'] == 'open')
	{
		echo l('index/close/' . $vars['name'], 'Close');
	}
	else 
	{
		echo l('index/open/' . $vars['name'], 'Open');
	} 
	?><br>
	<?php echo l('index/delete/' . $vars['name'], 'Delete'); ?><br>
</div>

</div>