<?php

require_once __DIR__ . '/vendor' . '/autoload.php';

use \Frs\FieldDefinition;
use \Frs\SessionManager;
use \Frs\Output\HtmlOutput;
use \Frs\Output\MailOutput;

$ho = new HtmlOutput(dirname(__FILE__) . '/templates');

$data = array(
    'session_time_left' => 0,
);

$sm = new SessionManager();

$action = '';
if (isset($_GET['action'])) {
    $action = $_GET['action'];
}
$data['action']    = $action;
$data['action_uc'] = ucwords($action);

if (isset($_GET['code']) && $_GET['code']) {
    $sm->authAndRedirect($_GET['code']);  // exits
}

$tpl_done = false;

// route pages that work with and without login
switch ($action) {
    case 'logout':
        $sm->logoutAndRedirect();  // exits
    case 'faq':
        $ho->setTemplate('faq_html');
        $tpl_done = true;
        break;
    case 'send':
        // Store input in session, in case the token timed out
        $sm->storeFormData($_POST['form_type']);
        break;
}

if (!$tpl_done && $sm->hasSessionToken()) {
    // Authenticated
    $created = $_SESSION['access_token']['created'];
    $expires = $_SESSION['access_token']['expires_in'];
    $expire_stamp = intval($created) + intval($expires);
    $data['session_created'] = $created;
    $data['session_expires'] = $expires;
    $data['session_time_left'] = ($expire_stamp) - time();

    try {
        $sm->verifySession();
    } catch (Exception $e) {
        echo $e->getMessage();
        #session_destroy();
        die();
    }

    $data['user'] = $sm->getUserinfo();
    $data['date_today'] = date('Y-m-d');

    // Check $userdata->verifiedEmail and deny if not verified.
    if (!$data['user']['verifiedEmail']) {
        $ho->setTemplate('notverified_html');
        $tpl_done = true;
    } else {
        switch ($action) {
            case 'send':
                echo 'This would send the mail...';
                $mo = new MailOutput(dirname(__FILE__) . '/templates');
                $mo->setTemplate('mail_' . $form_type);
                $skey = 'form_' . $form_type;
                $fd = new FieldDefinition($form_type);
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
            default:
                if (in_array($action, array('event', 'flight', 'hotel', 'restaurant', 'rentalcar'))) {
                    $ho->setTemplate($action . '_html');
                    $tpl_done = true;
                    $skey = 'form_' . $action;

                    $placeholders = array(
                        'USER_NAME'  => $data['user']['name_first'] . ' ' . $data['user']['name_last'],
                        'USER_EMAIL' => $data['user']['email'],
                    );
                    $fd = new FieldDefinition($action);
                    $fd->setPlaceholders($placeholders);
                    $fd->setFieldValues($_SESSION[$skey]);

                    $by_group = $fd->getGroups();

                    // Convert hash to list for Mustache compatibility
                    $by_group = array_values($by_group);
                    $data['form_data'] = $by_group;
                }
                if (!$tpl_done) {
                    $ho->setTemplate('loggedin_html');
                    $tpl_done = true;
                }
                break;
        }
    }
} elseif (!$tpl_done) {
    // Not authenticated
    $data['auth_url'] = $sm->getAuthUrl();
    $ho->setTemplate('index_html');
}

$ho->setTemplateVars($data);
$ho->sendOutputToStdout();
