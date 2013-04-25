<?php

class form extends router
{
    public static $output = '';
    public static $form = array();

    public function __construct($form)
    {
        // Validate and sanitize
        if (!isset($form['_init']['name'])) {
            trigger_error('The _init array has to be filled in', E_USER_ERROR);
        }

        $action = isset($form['_init']['action']) ? $form['_init']['action'] : '';

        self::$form = $form;

        // Check if we are in validation mode or in creation mode
        if ($_POST) {
            $this->validateForm();
        }

    }

    public function createForm()
    {
        $form = self::$form;

        $name = $form['_init']['name'];

        // Create a form id that can be used for validation
        $_SESSION['form_id_' . $name] = md5(time() . $name);

        self::$output .= '<form id="' . $name . '" name="' . $name . '" ';
        self::$output .= 'action="?q=' . $_GET['q'] . '" ';
        self::$output .= "method =\"POST\">\n";
        self::$output .= '<input type="hidden" name="form_id" value="' . $_SESSION['form_id_' . $name] . '">';

        unset($form['_init']);

        $this->iterateFormFields($form);

    }

    public function createFields()
    {
        $form = self::$form;
        unset($form['_init']);

        $this->iterateFormFields($form);

        return self::$output;
    }

    public function renderForm()
    {
        self::$output .= "</form>\n";

        return self::$output;
    }

    public function getResults()
    {
        $form = self::$form;
        if (!is_array($_SESSION['form_id_' . $form['_init']['name'] . '_values'])) {
            trigger_error('You are trying to reuse the data again', E_USER_ERROR);
        }
        $results = $_SESSION['form_id_' . $form['_init']['name'] . '_values'];
        unset($_SESSION['form_id_' . $form['_init']['name'] . '_values']);
        unset($results['form_id']);

        return $results;
    }

    private function addTextField($vars)
    {
        $this->validateName($vars);
        $this->prefixSkeleton($vars);

        self::$output .= '<input type="text" ';

        $this->argumentSkeleton($vars);
        if (isset($vars['_value'])) {
            self::$output .= 'value="' . $vars['_value'] . '" ';
        }

        self::$output .= ">\n";
        $this->suffixSkeleton($vars);
    }

    private function addPassword($vars)
    {
        $this->validateName($vars);
        $this->prefixSkeleton($vars);

        self::$output .= '<input type="password" ';

        $this->argumentSkeleton($vars);
        if (isset($vars['_value'])) {
            self::$output .= 'value="' . $vars['_value'] . '" ';
        }

        self::$output .= ">\n";
        $this->suffixSkeleton($vars);
    }

    private function addTextArea($vars)
    {
        $this->validateName($vars);
        $this->prefixSkeleton($vars);

        self::$output .= '<textarea ';

        $this->argumentSkeleton($vars);

        if (isset($vars['_rows'])) {
            self::$output .= ' rows="' . $vars['_rows'] . '" ';
        }

        if (isset($vars['_cols'])) {
            self::$output .= ' cols="' . $vars['_cols'] . '" ';
        }

        self::$output .= ">\n";

        if (isset($vars['_value'])) {
            self::$output .= $vars['_value'];
        }

        self::$output .= "</textarea>\n";
        $this->suffixSkeleton($vars);
    }

    private function addHidden($vars)
    {
        $this->validateName($vars);

        self::$output .= '<input type="hidden" ';

        $this->argumentSkeleton($vars);
        if (isset($vars['_value'])) {
            self::$output .= 'value="' . $vars['_value'] . '" ';
        }

        self::$output .= ">\n";
    }

    private function iterateFormFields($form)
    {
        foreach ($form as $key => $value) {
            // Get the values and put in the name there
            $type = $value['_type'];
            unset($value['_type']);
            $value['_name'] = $key;

            // Look for potential validation errors
            $value['_error'] = $this->getValidationError($value['_name']);
            $validatedvalue = $this->getValidationValue($value['_name']);

            if ($validatedvalue) {
                $value['_value'] = $validatedvalue;
            }

            switch ($type) {
                // Fieldset is special case
                case 'fieldset':
                    $value['_class'] = isset($value['_class']) ? $value['_class'] : $value['_name'];
                    self::$output .= '<fieldset id="' . $value['_name'] . '" class="' . $value['_class'] . '">';
                    if (isset($value['_label'])) { self::$output .= '<h2>' . $value['_label'] . '</h2>'; }
                    unset($value['_label']);
                    unset($value['_name']);
                    unset($value['_class']);
                    unset($value['_error']);
                    // Recurse
                    $this->iterateFormFields($value);
                    self::$output .= '</fieldset>';
                    break;
                // Nested is a special case
                case 'nested':
                    if (isset($value['_script'])) {
                        self::$output .= '<script>' . $value['_script'] . '</script>';
                    }
                    $name = $value['_name'];
                    self::$output .= '<div id="' . str_replace('.', '_-_', $name) . '" class="form-nested"><h3>';
                    self::$output .= $name . ' </h3><div class="data">';
                    unset($value['_label']);
                    unset($value['_script']);
                    unset($value['_name']);
                    unset($value['_error']);
                    $this->iterateFormFields($value);
                    self::$output .= '</div><span class="button">+ Add ' . $name . ' object</span></div>';
                    break;
                    break;
                case 'hidden':
                    $this->addHidden($value);
                    break;
                case 'textField':
                    $this->addTextField($value);
                    break;
                case 'password':
                    $this->addPassword($value);
                    break;
                case 'textArea':
                    $this->addTextArea($value);
                    break;
                case 'select':
                    $this->addSelect($value);
                    break;
                case 'checkbox':
                    $this->addCheckbox($value);
                    break;
                case 'checkboxes':
                    $this->addCheckboxes($value);
                    break;
                case 'radios':
                    $this->addRadios($value);
                    break;
                case 'submit':
                    $this->addSubmit($value);
                    break;
                case 'button':
                    $this->addButton($value);
                    break;
                case 'file':
                    $this->addFile($value);
                    break;
            }
        }
    }

    private function addSelect($vars)
    {
        $this->validateName($vars);
        $this->validateOptions($vars);

        $this->prefixSkeleton($vars);

        self::$output .= '<select ';
        $this->argumentSkeleton($vars);
        self::$output .= ">\n";

        foreach ($vars['_options'] as $key => $value) {
            $selected = '';
            if(isset($vars['_value']) && $vars['_value'] == $key) $selected = 'selected';
            self::$output .= "<option value=\"$key\" $selected>$value\n";
        }

        self::$output .= "</select>\n";

        $this->suffixSkeleton($vars);
    }

    private function addCheckbox($vars)
    {
        $this->validateName($vars);
        self::$output .= '<div class="input-form input-checkbox input-checkbox-' . $vars['_name'] . '">';
        self::$output .= '<input type="checkbox" ';

        $this->argumentSkeleton($vars);

        $extraoutput = '';
        // Tooltip
        if (isset($vars['_description'])) {
            $extraoutput = '<a href="#" class="tooltip" title="' . $vars['_description'] . '"><span class="infoicon"></span></a>';
        }

        if (isset($vars['_value']) && $vars['_value']) {
            self::$output .= 'checked  ';
        }

        self::$output .= "><label for=\"". $vars['_name'] . "\">" . $vars['_label'] . "$extraoutput</label></div>\n";
    }

    private function addCheckboxes($vars)
    {
        $this->validateName($vars);
        $this->validateOptions($vars);

        $this->prefixSkeleton($vars);

        $vars['_name'] = isset($vars['_alternative_name']) ? $vars['_alternative_name'] : $vars['_name'];
        foreach ($vars['_options'] as $key => $value) {
            $checked = isset($vars['_value']) && is_array($vars['_value']) && in_array($key, $vars['_value']) ? 'checked' : '';
            self::$output .= '<input type="checkbox" name="' . $vars['_name'] . '[]" value="' . $key . '" id="' . $key . '" ' . $checked .  '><label for="' . $key . '">' . $value . '</label>';
        }

        $this->suffixSkeleton($vars);
    }

    private function addRadios($vars)
    {
        $this->validateName($vars);
        $this->validateOptions($vars);

        $this->prefixSkeleton($vars);

        $vars['_name'] = isset($vars['_alternative_name']) ? $vars['_alternative_name'] : $vars['_name'];
        foreach ($vars['_options'] as $key => $value) {
            $checked = '';
            if(isset($vars['_value']) && $vars['_value'] == $key) $checked = 'checked';
            self::$output .= '<input type="radio" ' . $checked . ' name="' . $vars['_name'] . '" id="' . $key . '" value="' . $key . '"><label for="' . $key . '">' . $value . '</label>';
        }

        $this->suffixSkeleton($vars);
    }

    private function addSubmit($vars = array())
    {
        $vars['_name'] = isset($vars['_name']) ? $vars['_name'] : 'submit';

        self::$output .= "<div class=\"submit\">\n";

        self::$output .= '<input type="submit" ';

        $this->argumentSkeleton($vars);
        if (isset($vars['_value'])) {
            self::$output .= 'value="' . $vars['_value'] . '" ';
        }

        self::$output .= ">\n";

        $this->suffixSkeleton($vars);
    }

    private function addButton($vars = array())
    {
        $vars['_name'] = isset($vars['_name']) ? $vars['_name'] : 'submit';

        self::$output .= "<div class=\"formbutton\">\n";

        self::$output .= '<input type="button" ';

        $this->argumentSkeleton($vars);
        if (isset($vars['_value'])) {
            self::$output .= 'value="' . $vars['_value'] . '" ';
        }

        self::$output .= ">\n";

        $this->suffixSkeleton($vars);
    }

    private function prefixSkeleton($vars)
    {
        self::$output .= '<div class="input-form input-' . $vars['_name'] . "\">\n";
        if (isset($vars['_label'])) {
            $extraoutput = '';
            // Tooltip
            if (isset($vars['_description'])) {
                $extraoutput = '<a href="#" class="tooltip" title="' . $vars['_description'] . '"><span class="infoicon"></span></a>';
            }

            self::$output .= '<label for="' . $vars['_name'] . '">' . $vars['_label'] . ": </label>$extraoutput\n";
        }
    }

    private function argumentSkeleton($vars)
    {
        if (isset($vars['_class'])) {
            self::$output .= 'class="' . $vars['_class'] . '" ';
        }

        if (isset($vars['_disabled']) && $vars['_disabled']) {
            self::$output .= 'disabled ';
        }

        if (isset($vars['_style'])) {
            self::$output .= 'style="' . $vars['_style'] . '" ';
        }

        self::$output .= 'id="' . $vars['_name'] . '" ';
        $vars['_name'] = isset($vars['_alternative_name']) ? $vars['_alternative_name'] : $vars['_name'];
        self::$output .= 'name="' . $vars['_name'] . '" ';
    }

    private function suffixSkeleton($vars)
    {
        self::$output .= "</div>\n";

        // Print error message if it exists
        if (count($vars['_error'])) {
            self::$output .= "<ul class=\"formError\">\n";
            foreach ($vars['_error'] as $message) {
                self::$output .= "<li>$message</li>\n";
            }
            self::$output .= "</ul>\n";
        }
    }

    private function validateName($vars)
    {
        if (!isset($vars['_name'])) {
            trigger_error('Name has to be given', E_USER_ERROR);
        }
    }

    private function validateForm()
    {
        $form = self::$form;
        $values = $_POST;
        $error['error'] = false;
        $init = $form['_init'];
        unset($form['_init']);

        // Check so the form id is the same as the original form
        if (!isset($_SESSION['form_id_' . $init['name']])) {
            trigger_error('No form id was sent, aborting', E_USER_ERROR);
        }

        if (!isset($values['form_id']) || $values['form_id'] != $_SESSION['form_id_' . $init['name']]) {
            trigger_error('The form id that was sent was wrong, aborting', E_USER_ERROR);
        }

        $this->validateItterate($values, $form, $error);

        $_SESSION['form_id_' . $init['name'] . '_values'] = $_POST;

        // If there has been an error, send it back. Otherwise send it to the "action"
        if ($error['error']) {
            $_SESSION['form_id_' . $init['name'] . '_error'] = $error['errors'];
            $this->redirect();
        } else {
            unset($_SESSION['form_id_' . $init['name'] . '_error']);
            $this->redirect($init['action']);
        }
    }

    private function validateItterate($values, $form, &$error)
    {
        if (is_array($form)) {
            foreach ($form as $name => $parts) {
                if (isset($parts['_validation']) && is_array($parts['_validation'])) {
                    foreach ($parts['_validation'] as $method => $data) {
                        switch ($method) {
                            case 'required':
                                if ($data) {
                                    if (!isset($values[$name]) || !$values[$name]) {
                                        $error['error'] = true;
                                        $error['errors'][$name][] = 'This field is required';
                                    }
                                }
                                break;
                        }
                    }
                }
                if (isset($parts['_type']) && $parts['_type'] == 'fieldset') {
                    $this->validateItterate($values, $parts, $error);
                }
            }
        }
    }

    private function validateOptions($vars)
    {
        if (!isset($vars['_options'])) {
            trigger_error('Options has to be given', E_USER_ERROR);
        }

        if (!is_array($vars['_options'])) {
            trigger_error('The options array is not an array', E_USER_ERROR);
        }

        if (array_keys($vars['_options']) === range(0, count($vars['_options']) - 1)) {
            trigger_error('The options array is not an associative array', E_USER_ERROR);
        }
    }

    private function getValidationValue($name)
    {
        $form = self::$form;
        if (isset($_SESSION['form_id_' . $form['_init']['name'] . '_values'][$name])) {
            return $_SESSION['form_id_' . $form['_init']['name'] . '_values'][$name];
        }

        return false;
    }

    private function getValidationError($name)
    {
        $form = self::$form;
        if (isset($_SESSION['form_id_' . $form['_init']['name'] . '_error'][$name])) {
            return $_SESSION['form_id_' . $form['_init']['name'] . '_error'][$name];
        }

        return array();
    }
}
