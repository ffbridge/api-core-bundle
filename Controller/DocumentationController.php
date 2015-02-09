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

        $target = $kernel->getRootDir().'/../web/doc/index.html';
        if (!$fs->exists($target)) {
            $blueprintManager = $this->container->get('kilix_api_core.blueprint_manager');
            $blueprintManager->generateDoc($kernel->getRootDir().'/../doc/api_doc.md', $target, true, 'doc/api', 'default');
        }

        return new Response(file_get_contents($target), 200, array());
    }

    public function postmanAction(Request $request)
    {
        $fs = new Filesystem();
        $kernel = $this->container->get('kernel');

        $target = $kernel->getRootDir().'/../doc/postman.json';
        if (!$fs->exists($target)) {
            $blueprintManager = $this->container->get('kilix_api_core.blueprint_manager');
            $blueprintManager->generatePostman($kernel->getRootDir().'/../doc/api_doc.md', $target, true, true, false, 'doc/api');
        }

        return new Response(file_get_contents($target), 200, array(
            'Content-Type' => 'application/json'
        ));
    }

    public function blueprintAction(Request $request)
    {
        $fs = new Filesystem();
        $kernel = $this->container->get('kernel');

        $target = $kernel->getRootDir().'/../doc/blueprint.json';
        if (!$fs->exists($target)) {
            $blueprintManager = $this->container->get('kilix_api_core.blueprint_manager');
            $blueprintManager->generateBlueprint($kernel->getRootDir().'/../doc/api_doc.md', $target, 'json', true, true, 'doc/api');
        }

        return new Response(file_get_contents($target), 200, array(
            'Content-Type' => 'application/json'
        ));
    }

    public function sourceAction(Request $request)
    {
        $fs = new Filesystem();
        $kernel = $this->container->get('kernel');

        $target = $kernel->getRootDir().'/../doc/api_doc_full.md';
        if (!$fs->exists($target)) {
            $blueprintManager = $this->container->get('kilix_api_core.blueprint_manager');
            $blueprintManager->concatenateDoc($kernel->getRootDir().'/../doc/api_doc.md', $target, 'doc/api');
        }

        return new Response(file_get_contents($target), 200, array(
            'Content-Type' => 'text/x-markdown'
        ));
    }
}
