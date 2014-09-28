<?php

namespace Kilix\Bundle\ApiCoreBundle\Tests\Fixtures\Bundles\ExampleBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Kilix\Bundle\ApiCoreBundle\Annotations\ApiParameters;

class ApiController extends Controller
{
    public function wsAction(Request $request)
    {
        $apiParameters = $request->attributes->get('api_parameters');

        return new JsonResponse($apiParameters->all());
    }

    /**
     * @ApiParameters(bag="Kilix\Bundle\ApiCoreBundle\Tests\Fixtures\Bundles\ExampleBundle\Api\ExampleApiParameterBag")
     * @param Request $request
     * @return JsonResponse
     */
    public function ws2Action(Request $request)
    {
        $apiParameters = $request->attributes->get('api_parameters');

        return new JsonResponse($apiParameters->all());
    }
}
