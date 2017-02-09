<?php

require_once __DIR__ . '/vendor' . '/autoload.php';

use \Frs\FieldDefinition;
use \Frs\Output\HtmlOutput;
use \Frs\Output\MailOutput;
use \Frs\Output\Transport\StdoutTransport;
use \Frs\Output\Transport\NullTransport;

$stdout = new StdoutTransport();
$ho = new HtmlOutput($stdout, dirname(__FILE__) . '/templates');

$action = '';
if (isset($_GET['action'])) {
    $action = $_GET['action'];
}
$ho->setTemplateVar('action', $action);
$ho->setTemplateVar('action_uc', ucwords($action));
$ho->setTemplateVar('date_today', date('Y-m-d'));

switch ($action) {
    case 'faq':
        $ho->setTemplate('faq_html');
        break;

    case 'send':
        $mo = new MailOutput(new NullTransport(), dirname(__FILE__) . '/templates');
        $form_type = $_REQUEST['form_type'];
        $mo->setTemplate('mail_' . $form_type);
        $fd = new FieldDefinition($form_type);
        $fd->setFieldValues($_POST);
        $fieldData = $fd->getFieldData();
        $fields = $fieldData['fields'];

        $data = $ho->getTemplateVars();
        $data['email_date'] = date('r');
        $data = array_merge($data, $fields);
        $mo->setTemplateVars($data);
        $mo->setTemplateVar('form_type', $form_type);
        $mo->setTemplateVar('form_type_uc', ucwords($form_type));
        $mo->setSubject('[FRS] ' . ucwords($form_type) . ' Reservation');   // default subject
        $mail_html = $mo->getRenderedOutput();  // contains headers + body
        list($headers, $mailbody) = preg_split('/\r?\n\r?\n/', $mail_html, 2);
        $mo->setHeadersFromString($headers);   // updates Subject line

        $ho->setTemplate('mail_prep_html');
        $ho->setTemplateVar('gscript_url', 'https://script.google.com/macros/s/AKfycbxVcugiTBTvWx8DK_HhuQh_vXdteir6GTXE_Anir3rfovatjQM/exec');
        $ho->setTemplateVar('mail_subject', $mo->getSubject());
        $ho->setTemplateVar('mail_body', $mailbody);
        break;

    case 'event':
    case 'flight':
    case 'hotel':
    case 'restaurant':
    case 'rentalcar':
    case 'train':
    case 'bus':
        $ho->setTemplate($action . '_html');
        $skey = 'form_' . $action;

        $fd = new FieldDefinition($action);
        $by_group = $fd->getGroups();

        // Convert hash to list for Mustache compatibility
        $by_group = array_values($by_group);
        $ho->setTemplateVar('form_data', $by_group);
        break;

    default:
        // Show welcome page
        $ho->setTemplate('welcome_html');
        break;
}

$ho->send();
