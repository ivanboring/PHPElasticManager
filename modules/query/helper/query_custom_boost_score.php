<?php
/**
 * @todo: Needs to add boosting
 */

class query_custom_boost_score extends query_base_model
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
        $name = 'custom_boost_factor';

    protected function constructBody()
    {
        $this->createHeader();
        $this->createMinus();

        $this->createNestDiv('query');
        $this->createLabel('query');
        $this->createPlusMultiple('query');
        $this->createCloseDiv();

        $this->createNestDiv('boost_factor');
        $this->createLabel('boost_factor');
        $this->createTextInput('setval');
        $this->createCloseDiv();

        return $this->output();
    }

}
