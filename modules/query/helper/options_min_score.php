<?php

class options_min_score extends query_base_model
{
	protected
		$parents = array(
			'root'
		),
		$type = 'option',
		$name = 'min_score';

	protected function constructBody()
	{
		$this->createLabel('min_score');
		$this->createTextInput('setval');
		$this->createMinus();
					
		return $this->output();
	}
}