<?php

class filter_nested extends query_base_model
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
		$name = 'nested';
		
	protected function constructBody()
	{
		$this->createHeader();
		$this->createMinus();

		$this->createNestDiv('path');
		$this->createLabel('path');
		$this->createTextInput('setval', 'longtext');
		$this->createCloseDiv();

		$this->createNestDiv('score_mode');
		$this->createLabel('score_mode');		
		$this->createSelect('setval', array(
			'' => 'default', 
			'max' => 'max',
			'total' => 'total',
			'avg' => 'avg',
			'none' => 'none'		
		));
		$this->createCloseDiv();
					
		$this->createNestDiv('query');
		$this->createLabel('query');
		$this->createPlus('query');
		$this->createCloseDiv();
		
		$this->createNestDiv('_cache');
		$this->createLabel('_cache');
		$this->createSelect('setval', array('' => 'default', 'true' => 'true', 'false' => 'false'));
		$this->createCloseDiv();
		
		return $this->output();
	}		

}