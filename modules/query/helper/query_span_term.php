<?php

class query_span_term extends query_base_model
{
	protected
		$parents = array(
			'include', 
			'exclude', 
			'clauses', 
			'match'
		),
		$type = 'query',
		$name = 'span_term';
		
	protected function constructBody()
	{
		$this->createHeader();
		$this->createMinus();
		
		$this->createNestDiv();
		$this->createGetField();
		$this->createBreakLine();
		
		$this->createNestDiv('', true);
		$this->createSelect('', array('value' => 'value', 'term' => 'term'));
		$this->createTextInput('setval', 'longtext');
		$this->createCloseDiv();

		$this->createNestDiv('boost');
		$this->createLabel('boost');
		$this->createTextInput('setval');
		$this->createCloseDiv();
						
		$this->createCloseDiv();
		
		return $this->output();
	}		

}