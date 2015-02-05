<?php

namespace Kilix\Bundle\ApiCoreBundle\EventListener;

use Kilix\Bundle\ApiCoreBundle\Controller\ApiErrorController;
use Kilix\Bundle\ApiCoreBundle\Request\ApiParameterBag;
use Doctrine\Common\Annotations\Reader;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Kilix\Bundle\ApiCoreBundle\Annotations\ApiParameters;
use Symfony\Component\Validator\ValidatorInterface;

class ApiParametersListener
{
    /**
     * @var Reader
     */
    protected $reader;

    /**
     * @var ValidatorInterface
     */
    protected $validator;

    public function __construct(Reader $reader, ValidatorInterface $validator)
    {
        $this->reader = $reader;
        $this->validator = $validator;
    }

    public function onKernelController(FilterControllerEvent $event)
    {
        $request = $event->getRequest();
        $apiValidation = false;
        if ($request->attributes->has('_api_bag')) {
            $apiBagClass = $request->attributes->get('_api_bag');
            $request->attributes->remove('_api_bag');
        }

        if ($request->attributes->has('_api_validation')) {
            $apiValidation = (bool) $request->attributes->get('_api_validation');
            $request->attributes->remove('_api_validation');
        }

        if (is_array($controller = $event->getController())) {
            $object = new \ReflectionObject($controller[0]);
            $method = $object->getMethod($controller[1]);

            foreach ($this->reader->getMethodAnnotations($method) as $configuration) {
                if ($configuration instanceof ApiParameters) {
                    if (isset($configuration->bag)) {
                        $apiBagClass = $configuration->bag;
                    }

                    if (isset($configuration->validation)) {
                        $apiValidation = (bool) $configuration->validation;
                    }
                }
            }
        }

        if (!empty($apiBagClass)) {
            $apiParameterBag = class_exists($apiBagClass) ? new $apiBagClass() : new ApiParameterBag();
            $apiParameterBag->populateFromRequest($request);
            $request->attributes->set('api_parameters', $apiParameterBag);

            if ($apiValidation) {
                $errors = $this->validator->validate($apiParameterBag);
                if (count($errors) > 0) {
                    $errorsList = array();
                    foreach ($errors as $error) {
                        $key = preg_replace('/parameters\[(.+)\]/', '$1', $error->getPropertyPath());

                        $errorsList[$key] = $error->getMessage();
                    }

                    $request->attributes->set('_api_errors', $errorsList);
                    $controller = new ApiErrorController();
                    $event->setController(array($controller, 'validationErrorsAction'));
                }
            }
        }
    }
}
