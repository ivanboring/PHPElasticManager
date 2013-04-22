<?php

class options_values extends query_base_model
{
	protected
		$parents = array(
			'values'
		),
		$type = 'option',
		$named = false,
		$name = 'addId';

	protected function constructBody()
	{
		$this->createTextInput('setval', 'longtext', 'Value');
		$this->createMinus();
		
		return $this->output();
	}
}