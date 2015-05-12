<?php

namespace Kilix\Bundle\ApiCoreBundle\Tests\Request;

use Kilix\Bundle\ApiCoreBundle\Request\PaginatedResponse;

class PaginatedResponseTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage The header Accept-Ranges must be set
     */
    public function testConstructorWithMissingAcceptRanges()
    {
        new PaginatedResponse('', 200, [
            'Content-Range' => '0-30/660',
            'Content-Location' => 'http://core.appli.com/route',
        ]);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage The header Content-Range must be set
     */
    public function testConstructorWithMissingContentRange()
    {
        new PaginatedResponse('', 200, [
            'Accept-Ranges' => 'items',
            'Content-Location' => 'http://core.appli.com/route',
        ]);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage The header Content-Location must be set with the current route
     */
    public function testConstructorWithMissingContentLocation()
    {
        new PaginatedResponse('', 200, [
            'Accept-Ranges' => 'items',
            'Content-Range' => '0-30/660',
        ]);
    }

    public function testGeneratePaginationHeaders()
    {
        $response = new PaginatedResponse('', 200, [
            'Accept-Ranges' => 'items',
            'Content-Range' => '0-30/660',
            'Content-Location' => 'http://core.appli.com/route',
        ]);
        $this->assertTrue($response->headers->has('Link'));
        $this->assertEquals('Accept-Ranges, Content-Range', $response->headers->get('Access-Control-Expose-Headers'));
        $this->assertEquals(
            '<http://core.appli.com/route>; rel="first"; items="0-30",'.
            '<http://core.appli.com/route>; rel="previous"; items="0-30",'.
            '<http://core.appli.com/route>; rel="next"; items="30-60",'.
            '<http://core.appli.com/route>; rel="last"; items="630-660"', $response->headers->get('Link'));
    }
}
