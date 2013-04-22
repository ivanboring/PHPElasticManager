<?php

class filter_and extends query_base_model
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
		$name = 'and';
		
	protected function constructBody()
	{
		$this->createHeader();
		$this->createMinus();
		
		$this->createNestDiv('filters');
		$this->createLabel('filters');
		$this->createPlusMultiple('filters');
		$this->createCloseDiv();
		
		$this->createNestDiv('_cache');
		$this->createLabel('_cache');
		$this->createSelect('setval', array('' => 'default', 'true' => 'true', 'false' => 'false'));
		$this->createCloseDiv();
		
		return $this->output();
	}		

}