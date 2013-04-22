<?php

class query_field extends query_base_model
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
		$name = 'field';
		
	protected function constructBody()
	{
		$this->createHeader();
		$this->createMinus();
		
		$this->createNestDiv();
		$this->createGetField();
		$this->createBreakLine();

		$this->createNestDiv('query');
		$this->createLabel('query');
		$this->createTextInput('setval', 'longtext');
		$this->createCloseDiv();

		$this->createNestDiv('boost');
		$this->createLabel('boost');
		$this->createTextInput('setval');
		$this->createCloseDiv();		
		
		$this->createNestDiv('enable_position_increments');
		$this->createLabel('enable_position_increments');
		$this->createSelect('setval', array(
			'' => 'default', 
			'false' => 'false', 
			'true' => 'true'			
		));
		$this->createCloseDiv();
								
		return $this->output();
	}		

}