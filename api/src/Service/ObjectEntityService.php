<?php

namespace App\Service;

use App\Entity\ObjectEntity;
use Conduction\CommonGroundBundle\Service\CommonGroundService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class ObjectEntityService
{
    private $em;
    private $commonGroundService;
    private $params;
    private $componentCode;
    private $entityName;
    private $uuid;

    public function __construct(EntityManagerInterface $em, CommonGroundService $commonGroundService, ParameterBagInterface $params)
    {
        $this->em = $em;
        $this->commonGroundService = $commonGroundService;
        $this->params = $params;
    }

    public function setEventVariables($componentCode, $entityName, $uuid)
    {
        $this->componentCode = $componentCode;
        $this->entityName = $entityName;
        $this->uuid = $uuid;
    }

    public function handlePost(ObjectEntity $objectEntity)
    {
        var_dump($this->componentCode);

        return $objectEntity;
    }

    public function handleGet(ObjectEntity $objectEntity)
    {
        var_dump($this->componentCode);

        return $objectEntity;
    }
}
