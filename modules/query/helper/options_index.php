<?php

class options_index extends query_base_model
{
	protected
		$parents = array(
			'index'
		),
		$type = 'option',
		$named = false,
		$name = 'addIndex';

	protected function constructBody()
	{
		$this->createGetIndexes('setval');
		$this->createMinus();
		
		return $this->output();
	}
}