<?php

use \Frs\FieldDefinition;

// FOR TESTING:
if (!isset($action)) {
    $action = 'hotel';
    $debug = true;
}
$skey   = 'form_' . $action;

$fd = new FieldDefinition($action);
$field_data = $fd->getFieldData();

$fields = array();

// Assign fields to groups, fill in (default) values
foreach ($field_data['fields'] as $key=>$meta) {
    $meta['field_id'] = $key;

    // Assign session value if set, or use default if set
    if (isset($_SESSION[$skey][$key])) {
        $meta['value'] = $_SESSION[$skey][$key];

        switch ($meta['type']) {
            case 'datetime':
                $meta['value_unixtime'] = strtotime($meta['value']);
                break;
        }
    }

    // Field type marker for Mustache
    $meta['fieldtype_' . $meta['type']] = true;

    // Add useful default values for some types
    if ($meta['type'] == 'datetime') {
        $meta['today'] = date('Y-m-d');
    }

    // Add to fieldlist
    $fields[$key] = $meta;
}

if ($debug) {
    print_r($fields);
}

$data['email_date'] = date('r');

$data = array_merge($data, $fields);
