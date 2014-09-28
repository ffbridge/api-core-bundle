<?php

namespace Kilix\Bundle\ApiCoreBundle\EventListener;

use Kilix\Bundle\ApiCoreBundle\Request\ApiParameterBag;
use Doctrine\Common\Annotations\Reader;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\HttpKernel;
use Kilix\Bundle\ApiCoreBundle\Annotations\ApiParameters;

class ApiParametersListener
{
    /**
     * @var Reader
     */
    private $reader;

    public function __construct($reader)
    {
        $this->reader = $reader;
    }

    public function onKernelController(FilterControllerEvent $event)
    {
        $request = $event->getRequest();
        if ($request->attributes->has('_api_bag')) {
            $apiBagClass = $request->attributes->get('_api_bag');
            $apiParameterBag = class_exists($apiBagClass) ? new $apiBagClass() : new ApiParameterBag();

            $apiParameterBag->populateFromRequest($request);

            $request->attributes->set('api_parameters', $apiParameterBag);
            $request->attributes->remove('_api_bag');
        }

        if (is_array($controller = $event->getController())) {
            $object = new \ReflectionObject($controller[0]);
            $method = $object->getMethod($controller[1]);

            foreach ($this->reader->getMethodAnnotations($method) as $configuration) {
                if (isset($configuration->bag)) {
                    $apiBagClass = $configuration->bag;
                    $apiParameterBag = class_exists($apiBagClass) ? new $apiBagClass() : new ApiParameterBag();

                    $apiParameterBag->populateFromRequest($request);

                    $request->attributes->set('api_parameters', $apiParameterBag);
                    $request->attributes->remove('_api_bag');
                }
            }
        }
    }
}
