<?php

class query_ids extends query_base_model
{
	protected
		$parents = array(
			'query', 
			'queries', 
			'must', 
			'must_not',
			'should',
			'positive',
			'no_match_query',
			'negative'
		),
		$type = 'query',
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