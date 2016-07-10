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

    public function testAddFieldValues()
    {
        $fdo = new FieldDefinition('hotel');
        $fdo->addFieldValues(array('url'=>'http://example.org/'), array('USER_NAME'=>'John Doe'));
        $fd = $fdo->getFieldData();
        $this->assertArrayHasKey('fields', $fd);
        $this->assertArrayHasKey('url', $fd['fields']);
        $this->assertArrayHasKey('underName_name', $fd['fields']);
        $this->assertEquals('http://example.org/', $fd['fields']['url']['value']);
        $this->assertEquals('John Doe', $fd['fields']['underName_name']['value']);
    }

    public function testSupportValues()
    {
        $fdo = new FieldDefinition('hotel');
        $fdo->addFieldValues(array('modifiedTime'=>'2016-01-01 01:01:01'));
        $fd = $fdo->getFieldData();
        $this->assertArrayHasKey('today', $fd['fields']['modifiedTime']);
        $this->assertEquals(date('Y-m-d'), $fd['fields']['modifiedTime']['today']);
    }

    public function testTranslatedValues()
    {
        ini_set('date.timezone', 'Europe/Berlin');
        $fdo = new FieldDefinition('hotel');
        $fdo->addFieldValues(array('modifiedTime'=>'2016-01-01 01:01:01'));
        $fd = $fdo->getFieldData();
        $this->assertArrayHasKey('value_unixtime', $fd['fields']['modifiedTime']);
        $this->assertEquals(1451606461, $fd['fields']['modifiedTime']['value_unixtime']);
    }
}
