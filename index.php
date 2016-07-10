<?php

require_once __DIR__ . '/vendor' . '/autoload.php';

use \Frs\FieldDefinition;
use \Frs\Output\HtmlOutput;
use \Frs\Output\MailOutput;

$ho = new HtmlOutput(dirname(__FILE__) . '/templates');

$data = array(
    'session_time_left' => 0,
);

$client = new Google_Client();
$client->setAuthConfigFile('client_secret.json');
$client->addScope(Google_Service_Oauth2::USERINFO_EMAIL);

session_start();

if (isset($_GET['action']) && $_GET['action'] == 'logout') {
    // Delete session and redirect to self
    #$client->setAccessToken($_SESSION['access_token']);
    #$client->revokeToken();   // removed granted permissions from account
    $_SESSION = array();
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time()-42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }
    session_destroy();
    header('Location: ' . $client->getRedirectUri());
    exit(0);
}

if (isset($_GET['code']) && $_GET['code']) {
    // Validate OAuth2 result, set access token and redirect to self
    $client->authenticate($_GET['code']);
    $_SESSION['access_token'] = $client->getAccessToken();
    header('Location: ' . $client->getRedirectUri());
    exit(0);
}

$tpl_done = false;

// route pages that work with and without login
if (isset($_GET['action'])) {
    switch ($_GET['action']) {
        case 'faq':
            $ho->setTemplate('faq_html');
            $tpl_done = true;
            break;
        case 'send':
            // Store input in session
            $form_type = $_POST['form_type'];
            $skey = 'form_' . $form_type;
            $_SESSION[$skey] = $_POST;
            break;
    }
}

if (!$tpl_done && isset($_SESSION['access_token']) && $_SESSION['access_token']) {
    // Authenticated
    $created = $_SESSION['access_token']['created'];
    $expires = $_SESSION['access_token']['expires_in'];
    $expire_stamp = intval($created) + intval($expires);
    $data['session_created'] = $created;
    $data['session_expires'] = $expires;
    $data['session_time_left'] = ($expire_stamp) - time();

    $client->setAccessToken($_SESSION['access_token']);
    if ($client->isAccessTokenExpired()) {
        // TODO: Redirect to $client->createAuthUrl(); to reauthenticate
        echo 'Token expired! <a href="' . $client->createAuthUrl() . '">Request new one</a>.';
        #session_destroy();
        die();
    }

    $oauth = new Google_Service_Oauth2($client);
    $userdata = $oauth->userinfo->get();

    $data['user'] = array(
        'name_first' => $userdata->givenName,
        'name_last'  => $userdata->familyName,
        'name'       => $userdata->name,
        'picture'    => $userdata->picture,
        'email'      => $userdata->email,
        'gender'     => $userdata->gender,
    );

    $data['date_today'] = date('Y-m-d');


    // Check $userdata->verifiedEmail and deny if not verified.
    if (!$userdata->verifiedEmail) {
        $ho->setTemplate('notverified_html');
        $tpl_done = true;
    } else {
        switch ($_GET['action']) {
            case 'send':
                echo 'This would send the mail...';
                $mo = new MailOutput(dirname(__FILE__) . '/templates');
                $mo->setTemplate('mail_' . $form_type);
                $action = $form_type;
                $skey = 'form_' . $action;
                $data['action'] = $action;
                $data['action_uc'] = ucwords($action);

                $fd = new FieldDefinition($action);
                $fd->setFieldValues($_SESSION[$skey]);
                $fieldData = $fd->getFieldData();
                $fields = $fieldData['fields'];

                $data['email_date'] = date('r');
                $data = array_merge($data, $fields);
                $mo->setTemplateVars($data);
                $mo->setSubject('[FRS] ' . $data['action_uc'] . ' Reservation');
                $mo->addRecipient($data['user']['email'], $data['user']['name_first'] . ' ' . $data['user']['name_last']);
                $mail_sent = $mo->send();
                if ($mail_sent) {
                    echo 'Mail sent successfully.';
                } else {
                    echo 'Mail sending failed!!';
                }
                break;
            case 'event':
                $ho->setTemplate('event_html');
                $tpl_done = true;
                break;
            case 'hotel':
                $ho->setTemplate('hotel_html');
                $tpl_done = true;
                $action = 'hotel';
                require 'prep_form.php';
                break;
            case 'restaurant':
                $ho->setTemplate('restaurant_html');
                $tpl_done = true;
                $action = 'restaurant';
                require 'prep_form.php';
                break;
            case 'rentalcar':
                $ho->setTemplate('rentalcar_html');
                $tpl_done = true;
                $action = 'rentalcar';
                require 'prep_form.php';
                break;
            default:
                if (!$tpl_done) {
                    $ho->setTemplate('loggedin_html');
                    $tpl_done = true;
                }
                break;
        }
    }
} elseif (!$tpl_done) {
    // Not authenticated
    $data['auth_url'] = $client->createAuthUrl();
    $ho->setTemplate('index_html');
}

$data['action'] = $_GET['action'];

$ho->setTemplateVars($data);
$ho->sendOutputToStdout();
