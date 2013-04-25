<?php

class options_script_fields extends query_base_model
{
    protected
        $parents = array(
            'root'
        ),
        $type = 'option',
        $name = 'script_fields';

    protected function constructBody()
    {
        $this->createLabel('script_fields');
        $this->createPlusMultipleObject('script_field');
        $this->createMinus();

        return $this->output();
    }
}
