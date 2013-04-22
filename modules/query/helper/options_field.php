<?php

class options_field extends query_base_model
{
	protected
		$parents = array(
			'fields'
		),
		$type = 'option',
		$named = false,
		$name = 'addField';

	protected function constructBody()
	{
		$this->createGetField('setval');
		$this->createMinus();
		
		return $this->output();
	}
}