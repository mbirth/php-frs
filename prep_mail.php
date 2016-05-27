<?php

// FOR TESTING:
if (!isset($action)) {
    $action = 'hotel';
    $debug = true;
}
$skey   = 'form_' . $action;

$field_data_json = file_get_contents('definitions/' . $action . '.json');
$field_data = json_decode($field_data_json, true);

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

$data = array_merge($data, $fields);
