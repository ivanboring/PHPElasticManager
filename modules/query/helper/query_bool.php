<?php
/**
 * @todo: Needs to add boosting
 */

class query_bool extends query_base_model
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
        $name = 'bool';

    protected function constructBody()
    {
        $this->createHeader();
        $this->createMinus();

        $this->createNestDiv('must');
        $this->createLabel('must');
        $this->createPlusMultiple('must');
        $this->createCloseDiv();

        $this->createNestDiv('must_not');
        $this->createLabel('must_not');
        $this->createPlusMultiple('must_not');
        $this->createCloseDiv();

        $this->createNestDiv('should');
        $this->createLabel('should');
        $this->createPlusMultiple('should');
        $this->createCloseDiv();

        $this->createNestDiv('minimum_number_should_match');
        $this->createLabel('minimum_number_should_match');
        $this->createTextInput('setval');
        $this->createCloseDiv();

        $this->createNestDiv('boost');
        $this->createLabel('boost');
        $this->createTextInput('setval');
        $this->createCloseDiv();

        $this->createCloseDiv();

        return $this->output();
    }

}
