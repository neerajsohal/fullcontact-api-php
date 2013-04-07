<?php

include_once 'creds.php';
include_once 'Services/FullContact.php';

/**
 * This class doesn't do much yet..
 *
 * @package  Services\FullContact
 * @author   Keith Casey <contrib@caseysoftware.com>
 * @license  http://www.apache.org/licenses/LICENSE-2.0 Apache
 */

class NameTest extends PHPUnit_Framework_TestCase
{
    protected $name = null;

    public function setUp()
    {
        global $apikey;

        $this->name = new Services_FullContact_Name($apikey);

        parent::setUp();
    }

    public function testNormalize()
    {
        $name = $this->name->normalize('John');
        $this->assertEquals('John', $name->nameDetails->givenName);
        $this->assertEquals('John', $name->nameDetails->fullName);

        $name = $this->name->normalize('John Smith');
        $this->assertEquals('John', $name->nameDetails->givenName);
        $this->assertEquals('Smith', $name->nameDetails->familyName);

        $name = $this->name->normalize('John Michael Smith');
        $this->assertEquals('John', $name->nameDetails->givenName);
        $this->assertEquals(1, count($name->nameDetails->middleNames));
        $this->assertEquals('Smith', $name->nameDetails->familyName);

        $name = $this->name->normalize('Mr. John Michael Smith');
        $this->assertEquals('John', $name->nameDetails->givenName);
        $this->assertEquals(1, count($name->nameDetails->prefixes));
        $this->assertEquals('John Michael Smith', $name->nameDetails->fullName);

        $name = $this->name->normalize('Mr. John Michael Smith Jr.');
        $this->assertEquals(1, count($name->nameDetails->prefixes));
        $this->assertEquals(1, count($name->nameDetails->suffixes));
        $this->assertEquals(0, count($name->nameDetails->nicknames));

        $name = $this->name->normalize('Mr. John (Johnny) Michael Smith Jr.');
        $this->assertEquals(1, count($name->nameDetails->nicknames));
        $this->assertEquals('John Michael Smith', $name->nameDetails->fullName);
    }
}