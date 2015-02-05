<?php

namespace Kilix\Bundle\ApiCoreBundle\Tests\Request;

use Kilix\Bundle\ApiCoreBundle\Request\ApiParameterBag;
use Symfony\Component\HttpFoundation\Request;

class ApiParameterBagTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ApiParameterBag
     */
    protected $bag;

    /**
     * @var ApiParameterBag
     */
    protected $extendedBag;

    protected function setUp()
    {
        $this->bag = new ApiParameterBag();
        $this->extendedBag = new TestApiParameterBag();
    }

    public function testPopulateFromRequest()
    {
        $request = new Request();
        $request->query->add(array('bar' => 'a', 'id' => 1));
        $request->request->add(array('foo' => 'b', 'content' => 'lorem'));
        $request->headers->add(array('sort' => '-id', 'foobar' => 'lorem'));

        $this->bag->populateFromRequest($request);
        $this->extendedBag->populateFromRequest($request);

        $this->assertEquals(array(
            'bar' => 'a',
            'id' => 1,
            'foo' => 'b',
            'content' => 'lorem',
            'sort' => '-id',
            'foobar' => 'lorem',
        ), $this->bag->all());

        $this->assertEquals(array(
            'bar' => 'a',
            'foo' => 'b',
            'sort' => '-id',
        ), $this->extendedBag->all());
    }
}

class TestApiParameterBag extends ApiParameterBag
{
    public function getFilteredKeys()
    {
        return array(
            'foo',
            'bar',
            'sort',
        );
    }
}
