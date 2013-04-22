<?php

class query_query_string extends query_base_model
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
		$name = 'query_string';
		
	protected function constructBody()
	{
		$this->createHeader();
		$this->createMinus();

		$this->createNestDiv('query');
		$this->createLabel('query');
		$this->createTextInput('setval', 'longtext');
		$this->createCloseDiv();
				
		$this->createNestDiv('fields');
		$this->createLabel('fields');
		$this->createPlusMultiple('fields');
		$this->createCloseDiv();
				
		$this->createNestDiv('default_operator');
		$this->createLabel('default_operator');
		$this->createSelect('setval', array('' => 'default', 'and' => 'AND', 'or' => 'OR'));
		$this->createCloseDiv();

		$this->createNestDiv('analyzer');
		$this->createLabel('analyzer');
		$this->createGetAnalyzer();
		$this->createCloseDiv();

		$this->createNestDiv('allow_leading_wildcard');
		$this->createLabel('allow_leading_wildcard');
		$this->createSelect('setval', array('' => 'default', 'true' => 'true', 'false' => 'false'));
		$this->createCloseDiv();

		$this->createNestDiv('lowercase_expanded_terms');
		$this->createLabel('lowercase_expanded_terms');
		$this->createSelect('setval', array('' => 'default', 'true' => 'true', 'false' => 'false'));
		$this->createCloseDiv();

		$this->createNestDiv('enable_position_increments');
		$this->createLabel('enable_position_increments');
		$this->createSelect('setval', array('' => 'default', 'true' => 'true', 'false' => 'false'));
		$this->createCloseDiv();
						
		$this->createNestDiv('fuzzy_max_expansions');
		$this->createLabel('fuzzy_max_expansions');
		$this->createTextInput('setval');
		$this->createCloseDiv();
						
		$this->createNestDiv('fuzzy_min_sim');
		$this->createLabel('fuzzy_min_sim');
		$this->createTextInput('setval');
		$this->createCloseDiv();
						
		$this->createNestDiv('fuzzy_prefix_length');
		$this->createLabel('fuzzy_prefix_length');
		$this->createTextInput('setval');
		$this->createCloseDiv();
						
		$this->createNestDiv('phrase_slop');
		$this->createLabel('phrase_slop');
		$this->createTextInput('setval');
		$this->createCloseDiv();
						
		$this->createNestDiv('boost');
		$this->createLabel('boost');
		$this->createTextInput('setval');
		$this->createCloseDiv();

		$this->createNestDiv('analyze_wildcard');
		$this->createLabel('analyze_wildcard');
		$this->createSelect('setval', array('' => 'default', 'true' => 'true', 'false' => 'false'));
		$this->createCloseDiv();

		$this->createNestDiv('auto_generate_phrase_queries');
		$this->createLabel('auto_generate_phrase_queries');
		$this->createSelect('setval', array('' => 'default', 'true' => 'true', 'false' => 'false'));
		$this->createCloseDiv();

		$this->createNestDiv('lenient');
		$this->createLabel('lenient');
		$this->createSelect('setval', array('' => 'default', 'true' => 'true', 'false' => 'false'));
		$this->createCloseDiv();
				
		$this->createNestDiv('minimum_should_match');
		$this->createLabel('minimum_should_match');
		$this->createTextInput('setval');
		$this->createCloseDiv();

		$this->createCloseDiv();
		
		return $this->output();
	}		

}