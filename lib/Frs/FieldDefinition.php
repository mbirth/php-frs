<?php

namespace Frs;

class FieldDefinition
{
    private $fieldData = array();

    /**
     * @param string $type Type of FieldDefinition (hotel, car, etc.)
     * @throws \Exception if no definition file for this $type is found
     */
    public function __construct($type)
    {
        $definitionFile = 'definitions/' . $type . '.json';
        if (!file_exists($definitionFile)) {
            throw new \Exception('File ' . $definitionFile . ' not found!');
        }
        $fieldDataJson  = file_get_contents($definitionFile);
        /** @var array */
        $this->fieldData = json_decode($fieldDataJson, true);
    }

    public function getFieldData()
    {
        return $this->fieldData;
    }

    public function getGroups()
    {
        $byGroup = array();
        foreach ($this->fieldData['groups'] as $id=>$group) {
            $byGroup[$group] = array(
                'group_name' => $group,
                'fields'     => array(),
            );
        }
        return $byGroup;
    }
}
