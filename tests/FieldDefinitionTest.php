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
        $fdo->setPlaceholder('USER_NAME', 'John Doe');
        $fdo->setFieldValues(array('url'=>'http://example.org/'));
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
        $fdo->setFieldValues(array('modifiedTime'=>'2016-01-01 01:01:01'));
        $fd = $fdo->getFieldData();
        $this->assertArrayHasKey('today', $fd['fields']['modifiedTime']);
        $this->assertEquals(date('Y-m-d'), $fd['fields']['modifiedTime']['today']);
    }

    public function testTranslatedValues()
    {
        ini_set('date.timezone', 'Europe/Berlin');
        locale_set_default('en-US');
        $fdo = new FieldDefinition('hotel');
        $fdo->setFieldValues(array('modifiedTime'=>'2016-01-01 01:01:01'));
        $fd = $fdo->getFieldData();
        $fmt = $fd['fields']['modifiedTime'];
        $this->assertArrayHasKey('value_unixtime', $fmt);
        $this->assertArrayHasKey('value_human', $fmt);
        $this->assertArrayHasKey('value_iso8601', $fmt);
        $this->assertArrayHasKey('value_rfc2822', $fmt);
        $this->assertEquals(1451606461, $fmt['value_unixtime']);
        $this->assertEquals('2016-01-01 01:01:01', $fmt['value_human']);
        $this->assertEquals('2016-01-01T01:01:01+01:00', $fmt['value_iso8601']);
        $this->assertEquals('Fri, 01 Jan 2016 01:01:01 +0100', $fmt['value_rfc2822']);
    }
}
