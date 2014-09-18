<?php

namespace Kilix\Bundle\ApiCoreBundle\EventListener;

use Kilix\Bundle\ApiCoreBundle\Request\ApiParameterBag;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\HttpKernel;

class ApiParametersListener
{
    public function onKernelRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();
        if ($request->attributes->has('_api_bag')) {
            $apiBagClass = $request->attributes->get('_api_bag');
            $apiParameterBag = class_exists($apiBagClass) ? new $apiBagClass() : new ApiParameterBag();

            $apiParameterBag->populateFromRequest($request);

            $request->attributes->set('api_parameters', $apiParameterBag);
            $request->attributes->remove('_api_bag');
        }
    }
}
