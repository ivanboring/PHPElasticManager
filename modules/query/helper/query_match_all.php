<?php

class query_match_all extends query_base_model
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
        $name = 'match_all';

    protected function constructBody()
    {
        $this->createHeader();
        $this->createMinus();

        $this->createNestDiv('boost');
        $this->createLabel('boost');
        $this->createTextInput('setval');
        $this->createCloseDiv();

        return $this->output();
    }
}
