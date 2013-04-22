<?php

class query_span_or extends query_base_model
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
		$name = 'span_or';
		
	protected function constructBody()
	{
		$this->createHeader();
		$this->createMinus();
		
		$this->createNestDiv('clauses');
		$this->createLabel('clauses');
		$this->createPlusMultiple('clauses');
		$this->createCloseDiv();
		
		return $this->output();
	}		

}