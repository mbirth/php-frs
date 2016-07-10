<?php

namespace Frs;

class FieldDefinition
{
    private $definitionsPrefix;
    private $fieldData = array();
    private $placeholders = array();

    /**
     * @param string $type Type of FieldDefinition (hotel, car, etc.)
     * @param string $definitionsPrefix Prefix for definition files. If it's a directory, has to end with slash!
     * @throws \Exception if no definition file for this $type is found
     */
    public function __construct($type, $definitionsPrefix = 'definitions/')
    {
        $this->definitionsPrefix = $definitionsPrefix;
        $definitionFile = $this->definitionsPrefix . $type . '.json';
        if (!file_exists($definitionFile)) {
            throw new \Exception('File ' . $definitionFile . ' not found!');
        }
        $fieldDataJson   = file_get_contents($definitionFile);
        $fieldData       = json_decode($fieldDataJson, true);
        if (!is_array($fieldData)) {
            $fieldData = array($fieldData);
        }
        $this->fieldData = $fieldData;
        $this->prepareFields();
    }

    public function getFieldData()
    {
        $this->processPlaceholders();
        return $this->fieldData;
    }

    public function getGroups()
    {
        $this->processPlaceholders();
        $byGroup = array();
        foreach ($this->fieldData['groups'] as $id=>$group) {
            $byGroup[$group] = array(
                'group_name' => $group,
                'fields'     => array(),
            );
        }

        foreach ($this->fieldData['fields'] as $key=>$meta) {
            $groupName = $this->fieldData['groups'][$meta['group']];
            $byGroup[$groupName]['fields'][] = $meta;
        }

        return $byGroup;
    }

    /**
     * Adds useful interpretations of $field['value'] if available.
     *
     * @param mixed[] &$field Field to enrich
     */
    private function addValueTranslations(&$field)
    {
        if (!isset($field['value'])) {
            return;
        }
        switch ($field['type']) {
            case 'datetime':
                $field['value_unixtime'] = strtotime($field['value']);
                break;
        }
    }

    /**
     * Adds useful default values for some $field['type']s.
     *
     * @param mixed[] &$field Field to enrich
     */
    private function addSupportValues(&$field)
    {
        switch ($field['type']) {
            case 'datetime':
                $field['min']   = date('Y-m-d');
                $field['today'] = date('Y-m-d');
                break;
        }
    }

    private function prepareFields()
    {
        foreach ($this->fieldData['fields'] as $key=>$meta) {
            $meta['field_id']   = $key;
            $groupName          = $this->fieldData['groups'][$meta['group']];
            $meta['group_name'] = $groupName;

            if (!isset($meta['value']) || empty($meta['value'])) {
                if (isset($meta['default'])) {
                    $meta['value'] = $meta['default'];
                } else {
                    $meta['value'] = '';
                }
            }

            // Field type marker for Mustache
            $meta['fieldtype_' . $meta['type']] = true;

            $this->addValueTranslations($meta);
            $this->addSupportValues($meta);

            // Add to fieldlist
            $this->fieldData['fields'][$key] = $meta;
        }
    }

    /**
     * Adds a placeholder token and the desired replacement value.
     *
     * @param string $placeholder Placeholder, e.g. USER_NAME
     * @param string $replacement Replacement value, e.g. John Doe
     */
    public function addPlaceholder($placeholder, $replacement)
    {
        $this->placeholders[$placeholder] = $replacement;
    }

    public function addPlaceholders($placeholders)
    {
        $this->placeholders = array_merge($this->placeholders, $placeholders);
    }

    public function processPlaceholders()
    {
        foreach ($this->fieldData['fields'] as $key=>$meta) {
            if (isset($this->placeholders[$meta['value']])) {
                $meta['value'] = $this->placeholders[$meta['value']];
            }
            $this->fieldData['fields'][$key] = $meta;
        }
    }

    /**
     * Adds the given $values or default values and replaces $placeholders. Also
     * adds some helping attributes to fields.
     *
     * @param mixed[] Key-value-array of values (value) to assign to fields (key)
     */
    public function addFieldValues($values = array())
    {
        foreach ($this->fieldData['fields'] as $key=>$meta) {
            // Assign session value if set, or use default if set
            if (isset($values[$key])) {
                $meta['value'] = $values[$key];
                $this->addValueTranslations($meta);
            }

            $this->addSupportValues($meta);

            // Add to fieldlist
            $this->fieldData['fields'][$key] = $meta;
        }
    }
}
