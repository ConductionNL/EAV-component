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
 * Class ObjectEntityController.
 */
class ObjectEntityController extends AbstractController
{
    /**
     * @Route("/test", methods={"POST"})
     * @Template
     */
    public function login(Request $request, CommonGroundService $commonGroundService, ParameterBagInterface $params, EventDispatcherInterface $dispatcher)
    {
        var_dump("test post");
        return [];
    }

    /**
     * @Route("/test", methods={"GET"})
     * @Template
     */
    public function logout(Request $request, CommonGroundService $commonGroundService, ParameterBagInterface $params, EventDispatcherInterface $dispatcher)
    {
        var_dump("test get");
        return [];
    }
}
