<?php

include_once 'creds.php';
include_once 'src/FullContact.php';
include_once 'Services/FullContact.php';

/**
 * This class doesn't do much yet..
 *
 * @package  Services\FullContact
 * @author   Keith Casey <contrib@caseysoftware.com>
 * @license  http://www.apache.org/licenses/LICENSE-2.0 Apache
 */

class FullContactAPITest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        global $apikey;

        $this->client = new FullContactAPI($apikey);

        parent::setUp();
    }

    public function testFailAPIKey()
    {
        $brokenRequest = new Services_FullContact_Name('nope, no good');

        $name = $brokenRequest->normalize('John');
        $this->assertEquals('403', $name->status);
        $this->assertRegExp('/invalid/i', $name->message);
    }

    public function testDoLookupEmail()
    {
        $result = $this->client->doLookup('bart@fullcontact.com');
        $this->assertArrayHasKey('status', $result);
        $this->assertEquals(200, $result['status']);
        $this->assertEquals('Lorang', $result['contactInfo']['familyName']);
        $this->assertGreaterThanOrEqual(11, count($result['socialProfiles']));
    }

    public function testBadEmail()
    {
        $result = $this->client->doLookup('bart@fullcontact');
        $this->assertEquals(422, $result['status']);
        $this->assertRegExp('/invalid/i', $result['message']);
    }

    public function testDoLookupPhone()
    {
        $result = $this->client->doLookup('3037170414', 'phone');
        $this->assertArrayHasKey('status', $result);
        $this->assertEquals(200, $result['status']);
        $this->assertEquals('Lorang', $result['contactInfo']['familyName']);
        $this->assertGreaterThanOrEqual(11, count($result['socialProfiles']));
    }

    public function testBadPhone()
    {
        $result = $this->client->doLookup('303717');
        $this->assertEquals(422, $result['status']);
        $this->assertRegExp('/invalid/i', $result['message']);
    }


    public function testDoLookupTwitter()
    {
        $result = $this->client->doLookup('lorangb', 'twitter');
        $this->assertArrayHasKey('status', $result);
        $this->assertEquals(200, $result['status']);
        $this->assertEquals('Lorang', $result['contactInfo']['familyName']);
        $this->assertGreaterThanOrEqual(11, count($result['socialProfiles']));
    }

    public function testDoLookupFacebook()
    {
        $result = $this->client->doLookup('bart.lorang', 'facebookUsername');
        $this->assertArrayHasKey('status', $result);
        $this->assertEquals(200, $result['status']);
        $this->assertEquals('Lorang', $result['contactInfo']['familyName']);
        $this->assertGreaterThanOrEqual(11, count($result['socialProfiles']));
    }
}