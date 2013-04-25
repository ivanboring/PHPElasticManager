<?php
/**
 * @todo: Needs to add boosting
 */

class query_boosting extends query_base_model
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
        $name = 'boosting';

    protected function constructBody()
    {
        $this->createHeader();
        $this->createMinus();

        $this->createNestDiv('positive');
        $this->createLabel('positive');
        $this->createPlusMultiple('positive');
        $this->createCloseDiv();

        $this->createNestDiv('negative');
        $this->createLabel('negative');
        $this->createPlusMultiple('negative');
        $this->createCloseDiv();

        $this->createNestDiv('negative_boost');
        $this->createLabel('negative_boost');
        $this->createTextInput('setval');
        $this->createCloseDiv();

        $this->createCloseDiv();

        return $this->output();
    }

}
