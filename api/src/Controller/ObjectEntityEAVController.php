<?php

// src/Controller/DefaultController.php

namespace App\Controller;

use Conduction\CommonGroundBundle\Service\CommonGroundService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class ObjectEntityEAVController.
 *
 * @Route("object_entities/eav")
 */
class ObjectEntityEAVController extends AbstractController
{
    /**
     * @Route("/test", methods={"GET"})
     */
    public function getTest(Request $request, CommonGroundService $commonGroundService, ParameterBagInterface $params, EventDispatcherInterface $dispatcher)
    {
        var_dump("test get");
        return [];
    }

    /**
     * @Route("/test", methods={"POST"})
     */
    public function postTest(Request $request, CommonGroundService $commonGroundService, ParameterBagInterface $params, EventDispatcherInterface $dispatcher)
    {
        $resource = $request->all();
        var_dump("test post");
        var_dump($resource);

        return [];
    }
}
