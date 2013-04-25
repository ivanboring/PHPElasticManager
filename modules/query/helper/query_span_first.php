<?php

class query_span_first extends query_base_model
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
        $name = 'span_first';

    protected function constructBody()
    {
        $this->createHeader();
        $this->createMinus();

        $this->createNestDiv('match');
        $this->createLabel('match');
        $this->createPlus('match');
        $this->createCloseDiv();

        $this->createNestDiv('end');
        $this->createLabel('end');
        $this->createTextInput('setval');
        $this->createCloseDiv();

        return $this->output();
    }

}
