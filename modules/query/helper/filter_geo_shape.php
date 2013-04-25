<?php

class filter_geo_shape extends query_base_model
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
        $name = 'geo_shape';

    protected function constructBody()
    {
        $this->createHeader();
        $this->createMinus();

        $this->createNestDiv();
        $this->createGetField();
        $this->createBreakLine();

        $this->createNestDiv();
        $this->createSelect('', array('shape' => 'shape', 'indexed_shape' => 'indexed_shape'));

        $this->createNestDiv('type');
        $this->createLabel('type');
        $this->createGetTypes();
        $this->createCloseDiv();

        $this->createNestDiv('index');
        $this->createLabel('index');
        $this->createGetIndexes();
        $this->createCloseDiv();

        $this->createNestDiv('id');
        $this->createLabel('id');
        $this->createTextInput('setval');
        $this->createCloseDiv();

        $this->createNestDiv('shape_field_name');
        $this->createLabel('shape_field_name');
        $this->createTextInput('setval', 'longtext');
        $this->createCloseDiv();

        $this->createNestDiv('coordinates');
        $this->createLabel('coordinates');
        $this->createTextInput('setval', 'longtext');
        $this->createCloseDiv();

        $this->createCloseDiv();
        $this->createCloseDiv();

        return $this->output();
    }

}
