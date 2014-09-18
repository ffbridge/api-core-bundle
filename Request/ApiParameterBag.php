<?php

namespace Kilix\Bundle\ApiCoreBundle\Request;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\HttpFoundation\Request;

class ApiParameterBag extends ParameterBag
{
    const PARAMETERS_TYPE_ATTRIBUTES = 'attributes';
    const PARAMETERS_TYPE_HEADERS = 'headers';
    const PARAMETERS_TYPE_QUERY = 'query';
    const PARAMETERS_TYPE_GET = 'query';
    const PARAMETERS_TYPE_REQUEST = 'request';
    const PARAMETERS_TYPE_POST = 'request';
    const PARAMETERS_TYPE_FILES = 'files';
    const PARAMETERS_TYPE_COOKIES = 'cookies';

    /**
     * @return array
     */
    private function getAllowedTypes()
    {
        return array(
            static::PARAMETERS_TYPE_HEADERS,
            static::PARAMETERS_TYPE_QUERY,
            static::PARAMETERS_TYPE_REQUEST,
            static::PARAMETERS_TYPE_COOKIES,
            static::PARAMETERS_TYPE_FILES,
            static::PARAMETERS_TYPE_ATTRIBUTES,
        );
    }

    /**
     * override this method to customize which request parameters bags will be used
     *
     * @return array
     */
    public function getFilteredType()
    {
        return array(
            static::PARAMETERS_TYPE_HEADERS,
            static::PARAMETERS_TYPE_QUERY,
            static::PARAMETERS_TYPE_REQUEST,
        );
    }

    /**
     * override this methods to select only some parameters key
     *
     * @return array
     */
    public function getFilteredKeys()
    {
        return array();
    }

    /**
     * @param Request $request
     */
    public function populateFromRequest(Request $request)
    {
        $filteredKeys = $this->getFilteredKeys();
        $allowedTypes = $this->getAllowedTypes();

        foreach ($this->getFilteredType() as $type) {
            if (in_array($type, $allowedTypes)) {
                $params = empty($filteredKeys) ?
                    $request->$type->all() :
                    array_intersect_key($request->$type->all(), array_flip($filteredKeys));

                if ($type == static::PARAMETERS_TYPE_HEADERS) {
                    $params = array_map(function($v) {
                        return is_array($v) ? implode(';', $v): $v;
                    }, $params);
                }

                $this->add($params);
            }
        }
    }
}
