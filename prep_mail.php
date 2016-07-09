<?php

use \Frs\FieldDefinition;

// FOR TESTING:
if (!isset($action)) {
    $action = 'hotel';
    $debug = true;
}
$skey   = 'form_' . $action;

$fd = new FieldDefinition($action);
$fd->addFieldValues($_SESSION[$skey]);

$fieldData = $fd->getFieldData();

$fields = $fieldData['fields'];

if ($debug) {
    print_r($fields);
}

$data['email_date'] = date('r');

$data = array_merge($data, $fields);
