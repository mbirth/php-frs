<?php

require_once __DIR__ . '/vendor' . '/autoload.php';

use \Frs\FieldDefinition;
use \Frs\SessionManager;
use \Frs\Output\HtmlOutput;
use \Frs\Output\MailOutput;
use \Frs\Output\Transport\StdoutTransport;

$stdout = new StdoutTransport();
$ho = new HtmlOutput($stdout, dirname(__FILE__) . '/templates');
$ho->setTemplateVar('session_time_left', 0);

$sm = new SessionManager();

$action = '';
if (isset($_GET['action'])) {
    $action = $_GET['action'];
}
$ho->setTemplateVar('action', $action);
$ho->setTemplateVar('action_uc', ucwords($action));

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
    $ho->setTemplateVar('session_created', $created);
    $ho->setTemplateVar('session_expires', $expires);
    $ho->setTemplateVar('session_time_left', ($expire_stamp) - time());

    try {
        $sm->verifySession();
    } catch (Exception $e) {
        echo $e->getMessage();
        #session_destroy();
        die();
    }

    $userInfo = $sm->getUserinfo();
    $ho->setTemplateVar('user', $userInfo);
    $ho->setTemplateVar('date_today', date('Y-m-d'));

    // Check $userdata->verifiedEmail and deny if not verified.
    if (!$userInfo['verifiedEmail']) {
        $ho->setTemplate('notverified_html');
        $tpl_done = true;
    } else {
        switch ($action) {
            case 'send':
                echo 'This would send the mail...';
                $mo = new MailOutput(dirname(__FILE__) . '/templates');
                $form_type = $_REQUEST['form_type'];
                $mo->setTemplate('mail_' . $form_type);
                $skey = 'form_' . $form_type;
                $fd = new FieldDefinition($form_type);
                $fd->setFieldValues($_SESSION[$skey]);
                $fieldData = $fd->getFieldData();
                $fields = $fieldData['fields'];

                $data = $ho->getTemplateVars();
                $data['email_date'] = date('r');
                $data = array_merge($data, $fields);
                $mo->setTemplateVars($data);
                $mo->setTemplateVar('form_type', $form_type);
                $mo->setTemplateVar('form_type_uc', ucwords($form_type));
                $mo->setSubject('[FRS] ' . ucwords($form_type) . ' Reservation');
                $mo->addRecipient($data['user']['email'], $data['user']['name_first'] . ' ' . $data['user']['name_last']);
                $mail_sent = $mo->send();
                if ($mail_sent) {
                    $ho->setTemplate('mail_sent_html');
                } else {
                    $ho->setTemplate('mail_failed_html');
                }
                break;
            default:
                if (in_array($action, array('event', 'flight', 'hotel', 'restaurant', 'rentalcar'))) {
                    $ho->setTemplate($action . '_html');
                    $tpl_done = true;
                    $skey = 'form_' . $action;

                    $placeholders = array(
                        'USER_NAME'  => $userInfo['name_first'] . ' ' . $userInfo['name_last'],
                        'USER_EMAIL' => $userInfo['email'],
                    );
                    $fd = new FieldDefinition($action);
                    $fd->setPlaceholders($placeholders);
                    $fd->setFieldValues($_SESSION[$skey]);

                    $by_group = $fd->getGroups();

                    // Convert hash to list for Mustache compatibility
                    $by_group = array_values($by_group);
                    $ho->setTemplateVar('form_data', $by_group);
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
    $ho->setTemplateVar('auth_url', $sm->getAuthUrl());
    $ho->setTemplate('index_html');
}

$ho->send();
