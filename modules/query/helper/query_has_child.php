<?php

class query_has_child extends query_base_model
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
		$name = 'has_child';
		
	protected function constructBody()
	{
		$this->createHeader();
		$this->createMinus();
		
		$this->createNestDiv('type');
		$this->createLabel('type');
		$this->createGetTypes('setval');
		$this->createCloseDiv();

		$this->createNestDiv('query');
		$this->createLabel('query');
		$this->createPlus('query');
		$this->createCloseDiv();

		$this->createNestDiv('_scope');
		$this->createLabel('_scope');
		$this->createTextInput('setval', 'longtext');
		$this->createCloseDiv();		
		
		$this->createNestDiv('score_type');
		$this->createLabel('score_type');
		$this->createSelect('setval', array(
			'' => 'default', 
			'max' => 'max', 
			'sum' => 'sum',
			'avg' => 'avg', 
			'none' => 'none'			
		));
		$this->createCloseDiv();
								
		return $this->output();
	}		

}