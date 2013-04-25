<?php

class filter_match_all extends query_base_model
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
        $name = 'match_all';

    protected function constructBody()
    {
        $this->createLabel('match_all');
        $this->createHidden('setval', ' ');
        $this->createMinus();

        return $this->output();
    }

}
