<?php

namespace Kilix\Bundle\ApiCoreBundle\Request;

use Symfony\Component\HttpFoundation\Response;

class PaginatedResponse extends Response
{
    /**
     * Constructor.
     *
     * @param mixed $content The response content, see setContent()
     * @param int   $status  The response status code
     * @param array $headers An array of response headers
     *
     * @throws \InvalidArgumentException When the HTTP status code is not valid
     *
     * @api
     */
    public function __construct($content = '', $status = 200, $headers = array())
    {
        parent::__construct($content, $status, $headers);
        
        if(!isset($headers['Accept-Ranges']))
        {
            throw new \InvalidArgumentException('The header Accept-Ranges must be set');
        }
        
        if(!isset($headers['Content-Range']))
        {
            throw new \InvalidArgumentException('The header Content-Range must be set');
        }
        
        if(!isset($headers['Content-Location']))
        {
            throw new \InvalidArgumentException('The header Content-Location must be set with the current route');
        }
        
        $this->generatePaginationHeaders($headers['Content-Range'], $headers['Content-Location']);
    }
    
    /**
     * @param string $contentRange
     * @param string $location
     */
    public function generatePaginationHeaders($contentRange, $location)
    {
        $data = explode('/', $contentRange);
        $ranges = explode('-', $data[0]);
        $minRange = $ranges[0];
        $maxRange = $ranges[1];
        $maxItems = $data[1];
        $limit = $maxRange - $minRange;
        
        $links = [
            'first' => "0-{$limit}",
            'previous' => ($minRange - $limit) . '-' . $minRange,
            'next' => $maxRange . '-' . ($maxRange + $limit),
            'last' => $maxItems - $limit . '-' . $maxItems
        ];
        // Avoid to set next link with smaller range than the min range
        if($minRange < $limit)
        {
            $links['previous'] = $links['first'];
        }
        // Avoid to set next link with bigger range than the max range
        if($maxRange + $limit > $maxItems)
        {
            $links['next'] = $links['last'];
        }
        $this->headers->add([
            'Link' =>
                "<$location>; rel=\"first\"; items=\"{$links['first']}\"," .
                "<$location>; rel=\"previous\"; items=\"{$links['previous']}\"," .
                "<$location>; rel=\"next\"; items=\"{$links['next']}\"," .
                "<$location>; rel=\"last\"; items=\"{$links['last']}\"",
            'Access-Control-Expose-Headers' => 'Accept-Ranges, Content-Range'
        ]);
    }
}