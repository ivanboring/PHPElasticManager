<?php

class sort_geo_distance extends query_base_model
{
	protected
		$parents = array(
			'sort'
		),
		$type = 'sort',
		$named = false,		
		$name = '_geo_distance';
		
	protected function constructBody()
	{
		$this->createNestDiv('_geo_distance', false, false);
		
		$this->createLabel('_geo_distance');
		
		$this->createNestDiv();
		$this->createGetField();
		$this->createPlus('geo_type');
		$this->createCloseDiv();
		
		$this->createNestDiv('order');
		$this->createLabel('order');
		$this->createSelect('setval', array(
			'asc' => 'ascending', 
			'desc' => 'descending'		
		));
		$this->createCloseDiv();


		$this->createNestDiv('unit');
		$this->createLabel('unit');
		$this->createTextInput('setval');
		$this->createCloseDiv();
		
		$this->createCloseDiv();
		$this->createMinus();
			
		return $this->output();
	}		

}