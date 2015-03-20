<?php

namespace Kilix\Bundle\ApiCoreBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ExampleApiControllerTest extends WebTestCase
{
    public function testRoutingApiParameters()
    {
        $client = static::createClient();

        $client->request('POST', '/example/api/ws-route?page=1&max=20&sort=name', array('created_at' => '2014-01-01'));
        $response = $client->getResponse();
        $this->assertEquals(array(
            'page' => '1',
            'max' => '20',
            'sort' => 'name',
            'created_at' => '2014-01-01',
        ), json_decode($response->getContent(), true));
    }

    public function testRoutingApiParametersWithValidation()
    {
        $client = static::createClient();

        $client->request('POST', '/example/api/ws-route-validation?page=test&max=20&sort=address', array('created_at' => '08/11/2014'));
        $response = $client->getResponse();

        $this->assertEquals(400, $response->getStatusCode());

        $this->assertEquals(array(
            'errors' => array(
                'page' => 'This value should be a valid number.',
                'sort' => 'The value you selected is not a valid choice.',
                'created_at' => 'This value is not a valid date.',
            ),
        ), json_decode($response->getContent(), true));
    }

    public function testAnnotationsApiParameters()
    {
        $client = static::createClient();

        $client->request('POST', '/example/api/ws-annotations?page=1&max=20&sort=name', array('created_at' => '2014-01-01'));
        $response = $client->getResponse();
        $this->assertEquals(array(
                'page' => '1',
                'max' => '20',
                'sort' => 'name',
                'created_at' => '2014-01-01',
            ), json_decode($response->getContent(), true));
    }

    public function testAnnotationsApiParametersWithValidation()
    {
        $client = static::createClient();

        $client->request('POST', '/example/api/ws-annotations-validation?page=test&max=20&sort=address', array('created_at' => '08/11/2014'));
        $response = $client->getResponse();

        $this->assertEquals(400, $response->getStatusCode());

        $this->assertEquals(array(
            'errors' => array(
                'page' => 'This value should be a valid number.',
                'sort' => 'The value you selected is not a valid choice.',
                'created_at' => 'This value is not a valid date.',
            ),
        ), json_decode($response->getContent(), true));
    }

    public function testAnnotationsApiParametersWithValidationXml()
    {
        $client = static::createClient();

        $client->request('POST', '/example/api/ws-annotations-validation?_format=xml&page=test&max=20&sort=address', array('created_at' => '08/11/2014'));
        $response = $client->getResponse();

        $this->assertEquals(400, $response->getStatusCode());

        $this->assertXmlStringEqualsXmlString(
            '<result>
                <errors>
                    <page>This value should be a valid number.</page>
                    <sort>The value you selected is not a valid choice.</sort>
                    <created_at>This value is not a valid date.</created_at>
                </errors>
            </result>',
            $response->getContent()
        );
    }

    public function testAnnotationsAsParam()
    {
        $client = static::createClient();

        $client->request('POST', '/example/api/ws-annotations-as?page=1&max=20&sort=name', array('created_at' => '2014-01-01'));
        $response = $client->getResponse();
        $this->assertEquals(array(
                'page' => '1',
                'max' => '20',
                'sort' => 'name',
                'created_at' => '2014-01-01',
            ), json_decode($response->getContent(), true));
    }
    
    public function testRoutinAsParam()
    {
        $client = static::createClient();

        $client->request('POST', '/example/api/ws-routing-as?page=1&max=20&sort=name', array('created_at' => '2014-01-01'));
        $response = $client->getResponse();
        $this->assertEquals(array(
                'page' => '1',
                'max' => '20',
                'sort' => 'name',
                'created_at' => '2014-01-01',
            ), json_decode($response->getContent(), true));
    }
    public function testErrorsWithSubCollections()
    {
        $client = static::createClient();

        $postArray = array(
            'filters' => array(
                'lastname' => 'test'
            )
        );

        $kernel = $client->getKernel();

        //print_r($kernel->getContainer()->get('kilix_api_core.content_type_listener'));

        $client->request(
            'POST', 
            '/example/api/sub-collection-test', 
            $postArray
        );

        $response = $client->getResponse();
        $data = json_decode($response->getContent(), true);

        $errorData = array(
            'errors' => array(
                'page' => 'search.missing_fields',
                'filters' => array(
                    'firstname' => 'filter.missing_fields'
                )
            )
        );

        print_r($data);
        $this->assertEquals($errorData, $data);
    }
}
