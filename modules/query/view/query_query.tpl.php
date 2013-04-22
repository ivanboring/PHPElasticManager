<?php echo $vars['form']; ?>

<?php if($vars['response']): ?>
<div class="box" style="text-align: left; margin-top: 20px;">
	<h3>Response:</h3>
	<pre>
<?php echo $vars['response']; ?>	
	</pre>
</div>	
<?php endif; ?>

	<form action="?q=query/load" method="post" id="fileform" enctype="multipart/form-data">
		<input type="file" name="file" id="file">
		<input type="submit" id="filesubmit" name="submit" value="submit">
	</form>