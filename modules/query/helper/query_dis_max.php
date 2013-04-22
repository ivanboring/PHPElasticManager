<?php

class query_dis_max extends query_base_model
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
		$name = 'dis_max';
		
	protected function constructBody()
	{
		$this->createHeader();
		$this->createMinus();
		
		$this->createNestDiv('queries');
		$this->createLabel('queries');
		$this->createPlusMultiple('queries');
		$this->createCloseDiv();

		$this->createNestDiv('boost');
		$this->createLabel('boost');
		$this->createTextInput('setval');
		$this->createCloseDiv();
						
		$this->createNestDiv('tie_breaker');
		$this->createLabel('tie_breaker');
		$this->createTextInput('setval');
		$this->createCloseDiv();
				
		return $this->output();
	}		

}