<?php

namespace Frs;

class SessionManager
{
    public function __construct()
    {
        session_start();
    }

    public function storeFormData($form_type)
    {
        $skey = 'form_' . $form_type;
        $_SESSION[$skey] = $_POST;
    }
}
