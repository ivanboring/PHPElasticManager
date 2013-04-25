<?php

class query_range extends query_base_model
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
        $name = 'range';

    protected function constructBody()
    {
        $this->createHeader();
        $this->createMinus();

        $this->createNestDiv();
        $this->createGetField();
        $this->createBreakLine();

        $this->createNestDiv('from');
        $this->createLabel('from');
        $this->createTextInput('setval', 'longtext');
        $this->createCloseDiv();

        $this->createNestDiv('to');
        $this->createLabel('to');
        $this->createTextInput('setval', 'longtext');
        $this->createCloseDiv();

        $this->createNestDiv('boost');
        $this->createLabel('boost');
        $this->createTextInput('setval');
        $this->createCloseDiv();

        $this->createNestDiv('include_lower');
        $this->createLabel('include_lower');
        $this->createSelect('setval', array(
            '' => 'default',
            'true' => 'true',
            'false' => 'false'
        ));
        $this->createCloseDiv();

        $this->createNestDiv('include_upper');
        $this->createLabel('include_upper');
        $this->createSelect('setval', array(
            '' => 'default',
            'true' => 'true',
            'false' => 'false'
        ));
        $this->createCloseDiv();

        $this->createCloseDiv();

        return $this->output();
    }

}
