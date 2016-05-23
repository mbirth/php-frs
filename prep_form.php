<?php

// FOR TESTING:
$action = 'hotel';
$skey   = 'form_' . $action;

$field_data_json = file_get_contents('definitions/' . $action . '.json');
$field_data = json_decode($field_data_json, true);

foreach ($field_data['groups'] as $id=>$group) {
    $by_group[$group] = array();
}

foreach ($field_data['fields'] as $key=>$meta) {
    $group_name = $field_data['groups'][$meta['group']];
    $meta['group_name'] = $group_name;
    if (isset($_SESSION[$skey][$key])) {
        $meta['value'] = $_SESSION[$skey][$key];
    } elseif (isset($meta['default'])) {
        switch ($meta['default']) {
            case 'USER_NAME':
                $meta['value'] = 'User Name';
                break;
            case 'USER_EMAIL':
                $meta['value'] = 'user@email.com';
                break;
            default:
                $meta['value'] = $meta['default'];
                break;
        }
    }
    $by_group[$group_name][$key] = $meta;
}
print_r($by_group);
