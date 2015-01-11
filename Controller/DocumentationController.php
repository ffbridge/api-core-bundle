<?php

namespace Kilix\Bundle\ApiCoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DocumentationController extends Controller
{
    public function showAction(Request $request)
    {
        $fs = new Filesystem();
        $kernel = $this->container->get('kernel');

        $index = $kernel->getRootDir().'/../web/doc/index.html';
        if (!$fs->exists($index)) {
            $aglio = $this->container->get('kilix_api_core.aglio');
            $aglio->generateDoc($kernel->getRootDir().'/../doc/api_doc.md', $index, true, 'doc/api', 'default');
        }

        $html = file_get_contents($index);
        $response = new Response($html, 200, array());

        return $response;
    }
}
