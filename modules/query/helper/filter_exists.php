<?php

class filter_exists extends query_base_model
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
		$name = 'exists';
		
	protected function constructBody()
	{
		$this->createHeader();
		$this->createMinus();
		
		$this->createNestDiv('field');
		$this->createLabel('field');
		$this->createGetField();
		$this->createCloseDiv();
		
		return $this->output();
	}		

}