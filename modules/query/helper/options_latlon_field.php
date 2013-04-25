<?php

class options_latlon_field extends query_base_model
{
    protected
        $parents = array(
            'top_left',
            'bottom_right',
            'geo_type'
        ),
        $type = 'option',
        $named = false,
        $name = 'latlon_field';

    protected function constructBody()
    {
        $this->createTextInput('setval', 'longtext');
        $this->createMinus();

        return $this->output();
    }
}
