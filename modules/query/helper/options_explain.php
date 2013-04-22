<?php

class options_explain extends query_base_model
{
	protected
		$parents = array(
			'root'
		),
		$type = 'option',
		$name = 'explain';

	protected function constructBody()
	{
		$this->createLabel('explain');
		$this->createSelect('setval', array('' => 'default', 'true' => 'true', 'false' => 'false'));
		$this->createMinus();
					
		return $this->output();
	}
}