<?php

class query_custom_filters_score extends query_base_model
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
		$name = 'custom_filters_score';
		
	protected function constructBody()
	{
		$this->createHeader();
		$this->createMinus();
		
		$this->createNestDiv('query');
		$this->createLabel('query');
		$this->createPlus('query');
		$this->createCloseDiv();

		$this->createNestDiv('filters');
		$this->createLabel('filters');
		$this->createPlusMultiple('filters');
		$this->createCloseDiv();		

		$this->createNestDiv('score_mode');
		$this->createLabel('score_mode');
		$this->createSelect('setval', array(
			'first' => 'first', 
			'min' => 'min', 
			'max' => 'max',
			'avg' => 'avg', 
			'total' => 'total', 
			'multiply' => 'multiply'			
		));
		$this->createCloseDiv();
		
		$this->createNestDiv('max_boost');
		$this->createLabel('max_boost');
		$this->createTextInput('setval');
		$this->createCloseDiv();

		$this->createNestDiv('script');
		$this->createLabel('script');
		$this->createTextInput('setval', 'longtext');
		$this->createCloseDiv();		
		
						
		return $this->output();
	}		

}