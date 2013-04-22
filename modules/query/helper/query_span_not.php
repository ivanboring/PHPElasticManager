<?php

/**
 * @todo The exclude does not work
 */
class query_span_not extends query_base_model
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
		$name = 'span_not';
		
	protected function constructBody()
	{
		$this->createHeader();
		$this->createMinus();
		
		$this->createNestDiv('include');
		$this->createLabel('include');
		$this->createPlusMultiple('include');
		$this->createCloseDiv();

		$this->createNestDiv('exclude');
		$this->createLabel('exclude');
		$this->createPlusMultiple('exclude');
		$this->createCloseDiv();
				
		return $this->output();
	}		

}