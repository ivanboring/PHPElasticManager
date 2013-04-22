<?php

class filter_missing extends query_base_model
{
	protected
		$parents = array(
			'filter', 
			'filters', 
			'and',
			'not', 
			'or',
			'must_filter',
			'must_not_filter',
			'should_filter'
		),
		$type = 'filter',
		$name = 'missing';
		
	protected function constructBody()
	{
		$this->createHeader();
		$this->createMinus();
		
		$this->createNestDiv('field');
		$this->createLabel('field');
		$this->createGetField('setval');
		$this->createCloseDiv();

		$this->createNestDiv('existence');
		$this->createLabel('existence');
		$this->createSelect('setval', array('' => 'default', 'true' => 'true', 'false' => 'false'));
		$this->createCloseDiv();

		$this->createNestDiv('null_value');
		$this->createLabel('null_value');
		$this->createSelect('setval', array('' => 'default', 'true' => 'true', 'false' => 'false'));
		$this->createCloseDiv();
						
		return $this->output();
	}		

}