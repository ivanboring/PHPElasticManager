<?php

class filter_ids extends query_base_model
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
		$name = 'ids';
		
	protected function constructBody()
	{
		$this->createHeader();
		$this->createMinus();
		
		$this->createNestDiv('type');
		$this->createLabel('type');
		$this->createGetTypes();
		$this->createCloseDiv();
		
		$this->createNestDiv('values');
		$this->createLabel('values');
		$this->createPlusMultiple('values');
		$this->createCloseDiv();
		
		return $this->output();
	}		

}