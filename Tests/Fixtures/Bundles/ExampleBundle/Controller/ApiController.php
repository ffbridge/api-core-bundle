<?php

namespace Kilix\Bundle\ApiCoreBundle\Tests\Fixtures\Bundles\ExampleBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class ApiController extends Controller
{
    public function wsAction(Request $request)
    {
        $apiParameters = $request->attributes->get('api_parameters');

        return new JsonResponse($apiParameters->all());
    }
}
