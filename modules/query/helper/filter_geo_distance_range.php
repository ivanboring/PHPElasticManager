<?php

class filter_geo_distance_range extends query_base_model
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
        $name = 'geo_distance_range';

    protected function constructBody()
    {
        $this->createHeader();
        $this->createMinus();

        $this->createNestDiv('from');
        $this->createLabel('from');
        $this->createTextInput('setval');
        $this->createCloseDiv();

        $this->createNestDiv('to');
        $this->createLabel('to');
        $this->createTextInput('setval');
        $this->createCloseDiv();

        $this->createNestDiv();
        $this->createGetField();
        $this->createPlus('geo_type');
        $this->createCloseDiv();

        $this->createNestDiv('include_upper');
        $this->createLabel('include_upper');
        $this->createSelect('setval', array('' => 'default', 'true' => 'true', 'false' => 'false'));
        $this->createCloseDiv();

        $this->createNestDiv('include_lower');
        $this->createLabel('include_lower');
        $this->createSelect('setval', array('' => 'default', 'true' => 'true', 'false' => 'false'));
        $this->createCloseDiv();

        return $this->output();
    }

}
