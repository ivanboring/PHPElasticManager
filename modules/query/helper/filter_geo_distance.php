<?php

class filter_geo_distance extends query_base_model
{
	protected
		$parents = array(
			'filter', 
			'filters', 
			'and',
			'not', 
			'or',
			'must_filter',
			'must_not_filter',
			'should_filter'
		),
		$type = 'filter',
		$name = 'geo_distance';
		
	protected function constructBody()
	{
		$this->createHeader();
		$this->createMinus();

		$this->createNestDiv('distance');
		$this->createLabel('distance');
		$this->createTextInput('setval');
		$this->createCloseDiv();

		$this->createNestDiv();
		$this->createGetField();
		$this->createPlus('geo_type');
		$this->createCloseDiv();
		
		$this->createNestDiv('distance_type');
		$this->createLabel('distance_type');
		$this->createSelect('setval', array('' => 'default', 'arc' => 'arc', 'plane' => 'plane'));
		$this->createCloseDiv();		

		$this->createNestDiv('optimize_bbox');
		$this->createLabel('optimize_bbox');
		$this->createSelect('setval', array('' => 'default', 'memory' => 'memory', 'indexed' => 'indexed', 'none' => 'none'));
		$this->createCloseDiv();
				
		return $this->output();
	}		

}

?>