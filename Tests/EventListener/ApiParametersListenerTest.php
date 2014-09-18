<?php

namespace Kilix\Bundle\ApiCoreBundle\Tests\EventListener;

use Kilix\Bundle\ApiCoreBundle\EventListener\ApiParametersListener;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class ApiParametersListenerTest extends \PHPUnit_Framework_TestCase
{
    public function testOnKernelRequest()
    {
        // prepare request fixture
        $request = new Request();

        $apiBagClass = 'Kilix\\Bundle\\ApiCoreBundle\\Tests\\Fixtures\\Bundles\\ExampleBundle\\Api\\ExampleApiParameterBag';

        $request->attributes->set('_api_bag', $apiBagClass);

        // create event mock
        $event = $this->getMockBuilder('Symfony\\Component\\HttpKernel\\Event\\GetResponseEvent')
            ->disableOriginalConstructor()
            ->getMock();

        $event->expects($this->any())
            ->method('getRequest')
            ->will($this->returnValue($request));

        $event->expects($this->any())
            ->method('getRequestType')
            ->will($this->returnValue(HttpKernelInterface::MASTER_REQUEST));

        // test listener
        $listener = new ApiParametersListener();

        $this->assertFalse($request->attributes->has('api_parameters'));
        $this->assertTrue($request->attributes->has('_api_bag'));

        $listener->onKernelRequest($event);

        $this->assertfalse($request->attributes->has('_api_bag'));
        $this->assertTrue($request->attributes->has('api_parameters'));

        $apiParameters = $request->attributes->get('api_parameters');

        $this->assertInstanceOf($apiBagClass , $apiParameters);
        $this->assertInstanceOf('Kilix\\Bundle\\ApiCoreBundle\\Request\\ApiParameterBag' , $apiParameters);
    }
}
