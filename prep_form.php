<?php

// FOR TESTING:
$action = 'hotel';
$skey   = 'form_' . $action;

$field_data_json = file_get_contents('definitions/' . $action . '.json');
$field_data = json_decode($field_data_json, true);

foreach ($field_data['groups'] as $id=>$group) {
    $by_group[$group] = array(
        'group_name' => $group,
        'fields' => array(),
    );
}

foreach ($field_data['fields'] as $key=>$meta) {
    $meta['field_id'] = $key;
    $group_name = $field_data['groups'][$meta['group']];
    $meta['group_name'] = $group_name;
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
    $meta['fieldtype_' . $meta['type']] = true;
    if ($meta['type'] == 'datetime') {
        $meta['min'] = date('Y-m-d');
    }
    $by_group[$group_name]['fields'][$key] = $meta;
}

foreach ($by_group as $group=>$group_data) {
    $by_group[$group]['fields'] = array_values($group_data['fields']);
}

#print_r($by_group);
$by_group = array_values($by_group);
$data['form_data'] = $by_group;
