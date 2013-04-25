<?php

class query_span_near extends query_base_model
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
        $name = 'span_near';

    protected function constructBody()
    {
        $this->createHeader();
        $this->createMinus();

        $this->createNestDiv('clauses');
        $this->createLabel('clauses');
        $this->createPlusMultiple('clauses');
        $this->createCloseDiv();

        $this->createNestDiv('slop');
        $this->createLabel('slop');
        $this->createTextInput('setval');
        $this->createCloseDiv();

        $this->createNestDiv('in_order');
        $this->createLabel('in_order');
        $this->createSelect('setval', array('' => 'default', 'true' => 'true', 'false' => 'false'));
        $this->createCloseDiv();

        $this->createNestDiv('collect_payloads');
        $this->createLabel('collect_payloads');
        $this->createSelect('setval', array('' => 'default', 'true' => 'true', 'false' => 'false'));
        $this->createCloseDiv();

        return $this->output();
    }

}
