<?php

namespace Frs;

class FieldDefinition
{
    private $field_data = array();

    /**
     * @param string $type Type of FieldDefinition (hotel, car, etc.)
     * @throws \Exception if no definition file for this $type is found
     */
    public function __construct($type)
    {
        $definition_file = 'definitions/' . $type . '.json';
        if (!file_exists($definition_file)) {
            throw new \Exception('File ' . $definition_file . ' not found!');
        }
        $field_data_json  = file_get_contents($definition_file);
        $this->field_data = json_decode($field_data_json, true);
    }

    public function getFieldData()
    {
        return $this->field_data;
    }

    public function getGroups()
    {
        $by_group = array();
        foreach ($this->field_data['groups'] as $id=>$group) {
            $by_group[$group] = array(
                'group_name' => $group,
                'fields'     => array(),
            );
        }
        return $by_group;
    }
}
