<?php
/**
 * @todo: Needs to add boosting
 */

class query_multi_match extends query_base_model
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
		$name = 'multi_match';

		
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

		$this->createNestDiv('use_dis_max');
		$this->createLabel('use_dis_max');
		$this->createSelect('setval', array('' => 'default', 'false' => 'false', 'true' => 'true'));
		$this->createCloseDiv();

		$this->createNestDiv('tie_breaker');
		$this->createLabel('tie_breaker');
		$this->createTextInput('setval');
		$this->createCloseDiv();	
		
		return $this->output();
	}
}
