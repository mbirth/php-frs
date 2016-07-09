<?php

use \Frs\FieldDefinition;

// FOR TESTING:
if (!isset($action)) {
    $action = 'hotel';
    $debug = true;
}
$skey   = 'form_' . $action;

$fd = new FieldDefinition($action);

$placeholders = array(
    'USER_NAME'  => $data['user']['name_first'] . ' ' . $data['user']['name_last'],
    'USER_EMAIL' => $data['user']['email'],
);

$fd->addFieldValues($_SESSION[$skey], $placeholders);

$by_group = $fd->getGroups();

// Convert hash to list for Mustache compatibility
$by_group = array_values($by_group);

if ($debug) {
    print_r($by_group);
}

$data['form_data'] = $by_group;
