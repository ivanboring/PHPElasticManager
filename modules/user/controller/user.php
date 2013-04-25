<?php

class controllerUser extends router
{
    public function __construct()
    {
    }
		
    public function page_login($args)
    {
        $form = new form($this->form_user_login($args));

        $form->createForm();

        $arguments['form'] = $form->renderForm();
        $vars['javascript'][] = 'custom/forms.js';
        $vars['content'] = $this->renderPart('login', $arguments);
        $vars['title'] = 'Login';

        return $vars;
    }

    public function page_login_post($args)
    {
        $form = new form($this->form_user_login($args));
        $results = $form->getResults();
        if (isset(parent::$config['users'][$results['username']]) && parent::$config['users'][$results['username']] == $results['password']) {
            $_SESSION['loggedin'] = true;
            $this->redirect('start');
        } else {
            $this->redirect('user/login');
        }

    }

    public function page_logout()
    {
        $_SESSION['loggedin'] = false;
        $this->redirect('user/login');
    }

    private function form_user_login($args)
    {

        $form['_init'] = array(
            'name' => 'user_login',
            'action' => 'user/login_post'
        );

        $form['login'] = array(
            '_type' => 'fieldset'
        );

        $form['login']['username'] = array(
            '_type' => 'textField',
            '_label' => 'Username',
            '_validation' => array(
                'required' => true
            ),
            '_description' => 'Your username.',
        );

        $form['login']['password'] = array(
            '_type' => 'password',
            '_label' => 'Password',
            '_validation' => array(
                'required' => true
            ),
            '_description' => 'Your password.',
        );

        $form['login']['submit'] = array(
            '_value' => 'Save document',
            '_type' => 'submit'
        );

        return $form;
    }
}
