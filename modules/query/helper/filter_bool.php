<?php

class filter_bool extends query_base_model
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
		$name = 'bool';
		
	protected function constructBody()
	{
		$this->createHeader();
		$this->createMinus();
		
		$this->createNestDiv('must');
		$this->createLabel('must');
		$this->createPlusMultiple('must_filter');
		$this->createCloseDiv();

		$this->createNestDiv('must_not');
		$this->createLabel('must_not');
		$this->createPlusMultiple('must_not_filter');
		$this->createCloseDiv();

		$this->createNestDiv('should');
		$this->createLabel('should');
		$this->createPlusMultiple('should_filter');
		$this->createCloseDiv();

		$this->createNestDiv('_cache');
		$this->createLabel('_cache');
		$this->createSelect('setval', array('' => 'default', 'true' => 'true', 'false' => 'false'));
		$this->createCloseDiv();
								
		$this->createCloseDiv();
		
		return $this->output();
	}		

}