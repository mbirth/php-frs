<?php

namespace Frs\Tests;

use \Frs\FieldDefinition;
#use \PHPUnit\Framework\TestCase;

class FieldDefinitionTest extends \PHPUnit_Framework_TestCase
{
    public function testLoading()
    {
        $fdo = new FieldDefinition('hotel');
        $fd  = $fdo->getFieldData();
        $this->assertArraySubset(array('groups'=>array(), 'fields'=>array()), $fd);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage File definitions/doesnotexist.json not found!
     */
    public function testLoadingFailure()
    {
        $fdo = new FieldDefinition('doesnotexist');
    }

    public function testGroups()
    {
        $fdo = new FieldDefinition('hotel');
        $byGroup = $fdo->getGroups();
        $this->assertArrayHasKey('Hotel Information', $byGroup);
        $this->assertArrayHasKey('Metadata', $byGroup);
    }
}
