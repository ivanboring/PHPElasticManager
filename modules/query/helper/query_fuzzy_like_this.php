<?php

class query_fuzzy_like_this extends query_base_model
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
		$name = 'fuzzy_like_this';
		
	protected function constructBody()
	{
		$this->createHeader();
		$this->createMinus();
		
		$this->createNestDiv('fields');
		$this->createLabel('fields');
		$this->createPlusMultiple('fields');
		$this->createCloseDiv();
		
		$this->createNestDiv('like_text');
		$this->createLabel('like_text');
		$this->createTextInput('setval', 'longtext');
		$this->createCloseDiv();

		$this->createNestDiv('max_query_terms');
		$this->createLabel('max_query_terms');
		$this->createTextInput('setval');
		$this->createCloseDiv();

		$this->createNestDiv('min_similarity');
		$this->createLabel('min_similarity');
		$this->createTextInput('setval');
		$this->createCloseDiv();

		$this->createNestDiv('prefix_length');
		$this->createLabel('prefix_length');
		$this->createTextInput('setval');
		$this->createCloseDiv();

		$this->createNestDiv('boost');
		$this->createLabel('boost');
		$this->createTextInput('setval');
		$this->createCloseDiv();

		$this->createNestDiv('ignore_tf');
		$this->createLabel('ignore_tf');
		$this->createSelect('setval', array('' => 'default', 'true' => 'true', 'false' => 'false'));
		$this->createCloseDiv();
		
		$this->createNestDiv('analyzer');
		$this->createLabel('analyzer');
		$this->createGetAnalyzer('setval');
		$this->createCloseDiv();
		
		return $this->output();
	}		

}