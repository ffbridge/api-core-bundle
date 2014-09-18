<?php

namespace Kilix\Bundle\ApiCoreBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ExampleApiControllerTest extends WebTestCase
{
    public function testExempleApiParameters()
    {
        $client = static::createClient();

        $client->request('POST', '/example/api/ws?page=1&max=20&sort=name', array('created_at' => '2014-01-01'));
        $response = $client->getResponse();
        $this->assertEquals(array(
            'page' => '1',
            'max' => '20',
            'sort' => 'name',
            'created_at' => '2014-01-01',
        ), json_decode($response->getContent(),true));
    }
}
