<?php

class query_more_like_this extends query_base_model
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
        $name = 'more_like_this';

    protected function constructBody()
    {
        $this->createHeader();
        $this->createMinus();

        $this->createNestDiv('fields');
        $this->createLabel('fields');
        $this->createPlusMultiple('fields');
        $this->createCloseDiv();

        $this->createNestDiv('like_text');
        $this->createLabel('like_text');
        $this->createTextInput('setval', 'longtext');
        $this->createCloseDiv();

        $this->createNestDiv('percent_terms_to_match');
        $this->createLabel('percent_terms_to_match');
        $this->createTextInput('setval');
        $this->createCloseDiv();

        $this->createNestDiv('min_term_freq');
        $this->createLabel('min_term_freq');
        $this->createTextInput('setval');
        $this->createCloseDiv();

        $this->createNestDiv('max_query_terms');
        $this->createLabel('max_query_terms');
        $this->createTextInput('setval');
        $this->createCloseDiv();

        $this->createNestDiv('min_doc_freq');
        $this->createLabel('min_doc_freq');
        $this->createTextInput('setval');
        $this->createCloseDiv();

        $this->createNestDiv('max_doc_freq');
        $this->createLabel('max_doc_freq');
        $this->createTextInput('setval');
        $this->createCloseDiv();

        $this->createNestDiv('min_word_length');
        $this->createLabel('min_word_length');
        $this->createTextInput('setval');
        $this->createCloseDiv();

        $this->createNestDiv('max_word_len');
        $this->createLabel('max_word_len');
        $this->createTextInput('setval');
        $this->createCloseDiv();

        $this->createNestDiv('boost_terms');
        $this->createLabel('boost_terms');
        $this->createTextInput('setval');
        $this->createCloseDiv();

        $this->createNestDiv('boost');
        $this->createLabel('boost');
        $this->createTextInput('setval');
        $this->createCloseDiv();

        $this->createNestDiv('analyzer');
        $this->createLabel('analyzer');
        $this->createGetAnalyzer('setval');
        $this->createCloseDiv();

        $this->createNestDiv('stop_words');
        $this->createLabel('stop_words');
        $this->createPlusMultiple('stop');
        $this->createCloseDiv();

        return $this->output();
    }

}
