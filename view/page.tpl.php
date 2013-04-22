<!DOCTYPE html>
<html>
<head>
<link rel="icon" type="image/png" href="resources/images/myicon.png">
<link rel="stylesheet" type="text/css" href="resources/css/style.css">
<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1.3.2/jquery.min.js"></script>
<script type="text/javascript" src="resources/js/qTip/jquery.qtip-1.0.0-rc3.min.js"></script>
<script type="text/javascript" src="resources/js/custom/core.js"></script>
<link href='http://fonts.googleapis.com/css?family=Open+Sans:400,300,600,700,800' rel='stylesheet' type='text/css'>
<?php
	if(isset($vars['javascript']) && is_array($vars['javascript']))
	{
		foreach($vars['javascript'] as $script)
		{
			echo '<script type="text/javascript" src="resources/js/' . $script . '"></script>' . "\n";
		}
	}
?>
<meta charset="UTF-8">
<title>PHPElasticManager: <?php echo $vars['title']; ?></title>
</head>

<body>
<div id="header" class="header">
	<div id="main">
		<?php echo l('', '<span><div class="logo"></div></span>'); ?>
		<div class="check">
			<input type="checkbox" id="validateQuery" <?php echo $vars['validate']; ?>><label for="validateQuery">Validate each query</label>
		</div>
	</div>
</div>

<div id="menu" class="menu">
	<div id="main">
	<ul>
	<?php foreach($vars['menus'] as $path => $values) { ?>
		<li><?php echo l($values['path'], $values['title']); ?></li>
	<?php } ?>
	</ul>
	</div>
</div>

<div id="main">
	<div id="leftmenu" class="leftMenu">
	<?php echo $vars['leftblock']; ?>
	</div>
	
	<div id="content" class="content">
	<h2><?php echo $vars['title']; ?></h2>
	<?php if($vars['response_message']): ?>
		<div class="response_message">
			<pre><?php echo $vars['response_message']; ?></pre>
		</div>
	<?php endif; ?>
	<?php echo $vars['content']; ?>
	</div>
</div>

</body>
</html>