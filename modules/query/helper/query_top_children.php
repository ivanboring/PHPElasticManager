<?php

class query_top_children extends query_base_model
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
        $name = 'top_children';

    protected function constructBody()
    {
        $this->createHeader();
        $this->createMinus();

        $this->createNestDiv('type');
        $this->createLabel('type');
        $this->createGetTypes('setval');
        $this->createCloseDiv();

        $this->createNestDiv('query');
        $this->createLabel('query');
        $this->createPlus('query');
        $this->createCloseDiv();

        $this->createNestDiv('factor');
        $this->createLabel('factor');
        $this->createTextInput('setval');
        $this->createCloseDiv();

        $this->createNestDiv('incremental_factor');
        $this->createLabel('incremental_factor');
        $this->createTextInput('setval');
        $this->createCloseDiv();

        $this->createNestDiv('score');
        $this->createLabel('score');
        $this->createSelect('setval', array(
            '' => 'default',
            'max' => 'max',
            'sum' => 'sum',
            'avg' => 'avg'
        ));
        $this->createCloseDiv();

        $this->createNestDiv('_scope');
        $this->createLabel('_scope');
        $this->createTextInput('setval', 'longtext');
        $this->createCloseDiv();

        return $this->output();
    }

}
