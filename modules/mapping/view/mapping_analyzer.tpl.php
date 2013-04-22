<div class="box arrows">
	<ul>
	<li><strong>Type: </strong> <?php echo $vars['type']; ?></li>
	<li><strong>Tokenizer: </strong> <?php echo $vars['tokenizer']; ?></li>
	<li><strong>Filter:</strong><ul>
	<?php foreach($vars['filter'] as $key => $value) {?>
		<li><strong><?php echo $key; ?></strong> 
		<?php if(is_array($value)): ?>
			<ul>
			<?php foreach($value as $filterkey => $filtervalue) { ?>
				<li><strong><?php echo ucfirst($filterkey); ?>:</strong>
				<?php if(is_array($filtervalue)): ?>
					<ul>
					<?php foreach($filtervalue as $vals) { ?>
						<li><?php echo $vals; ?></li>
					<?php } ?>	
					</ul>
				<?php else: ?>
					<?php echo $filtervalue; ?>
				<?php endif; ?>	
				</li>
			<?php } ?>	
			</ul>
		<?php endif; ?>
		</li>
	<?php } ?>
	</ul></li>
	</ul>
</div>
