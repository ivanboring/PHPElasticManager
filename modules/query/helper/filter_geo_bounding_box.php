<?php

class filter_geo_bounding_box extends query_base_model
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
		$name = 'geo_bounding_box';
		
	protected function constructBody()
	{
		$this->createHeader();
		$this->createMinus();

		$this->createNestDiv();
		$this->createGetField();
		$this->createBreakLine();
		
		$this->createNestDiv('top_left');
		$this->createLabel('top_left');
		$this->createPlus('top_left');
		$this->createCloseDiv();

		$this->createNestDiv('bottom_right');
		$this->createLabel('bottom_right');
		$this->createPlus('bottom_right');
		$this->createCloseDiv();		
				
		$this->createCloseDiv();
		$this->createCloseDiv();
		
		return $this->output();
	}		

}

?>