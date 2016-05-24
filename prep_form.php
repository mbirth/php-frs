<?php

// FOR TESTING:
if (!isset($action)) {
    $action = 'hotel';
    $debug = true;
}
$skey   = 'form_' . $action;

$field_data_json = file_get_contents('definitions/' . $action . '.json');
$field_data = json_decode($field_data_json, true);

// Build Group list
foreach ($field_data['groups'] as $id=>$group) {
    $by_group[$group] = array(
        'group_name' => $group,
        'fields' => array(),
    );
}

// Assign fields to groups, fill in (default) values
foreach ($field_data['fields'] as $key=>$meta) {
    $meta['field_id'] = $key;
    $group_name = $field_data['groups'][$meta['group']];
    $meta['group_name'] = $group_name;

    // Assign session value if set, or use default if set
    if (isset($_SESSION[$skey][$key])) {
        $meta['value'] = $_SESSION[$skey][$key];
    } elseif (isset($meta['default'])) {
        switch ($meta['default']) {
            case 'USER_NAME':
                $meta['value'] = $data['user']['name_first'] . ' ' . $data['user']['name_last'];
                break;
            case 'USER_EMAIL':
                $meta['value'] = $data['user']['email'];
                break;
            default:
                $meta['value'] = $meta['default'];
                break;
        }
    }

    // Field type marker for Mustache
    $meta['fieldtype_' . $meta['type']] = true;

    // Add useful default values for some types
    if ($meta['type'] == 'datetime') {
        $meta['min'] = date('Y-m-d');
    }

    // Add to fieldlist
    $by_group[$group_name]['fields'][] = $meta;
}

// Convert hash to list for Mustache compatibility
$by_group = array_values($by_group);

if ($debug) {
    print_r($by_group);
}

$data['form_data'] = $by_group;
