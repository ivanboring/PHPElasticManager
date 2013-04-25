<?php

class query_nested extends query_base_model
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
        $name = 'nested';

    protected function constructBody()
    {
        $this->createHeader();
        $this->createMinus();

        $this->createNestDiv('path');
        $this->createLabel('path');
        $this->createTextInput('setval', 'longtext');
        $this->createCloseDiv();

        $this->createNestDiv('score_mode');
        $this->createLabel('score_mode');
        $this->createSelect('setval', array(
            '' => 'default',
            'max' => 'max',
            'total' => 'total',
            'avg' => 'avg',
            'none' => 'none'
        ));
        $this->createCloseDiv();

        $this->createNestDiv('query');
        $this->createLabel('query');
        $this->createPlus('query');
        $this->createCloseDiv();

        return $this->output();
    }

}
