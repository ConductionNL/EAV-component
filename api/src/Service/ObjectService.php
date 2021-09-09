<?php

namespace App\Service;

use App\Entity\Attribute;
use App\Entity\Entity;
use App\Entity\ObjectEntity;
use App\Entity\Value;
use Conduction\CommonGroundBundle\Service\CommonGroundService;
use Doctrine\ORM\EntityManagerInterface;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use SensioLabs\Security\Exception\HttpException;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Paginator;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\String\Inflector\EnglishInflector;

class ObjectService
{
    private EntityManagerInterface $em;
    private CommonGroundService $commonGroundService;
    private ParameterBagInterface $params;
    private ValidationService $validationService;
    private CallService $callService;
    private SaveService $saveService;
    private GetService $getService;
    private string $componentCode;
    private string $entityName;
    private ?string $uuid;
    private array $body;
    private array $validationResults;
    private array $apiResults;

    public function __construct(EntityManagerInterface $em, CommonGroundService $commonGroundService, ParameterBagInterface $params, ValidationService $validationService, CallService $callService, SaveService $saveService, GetService $getService)
    {
        $this->em = $em;
        $this->commonGroundService = $commonGroundService;
        $this->params = $params;
        $this->validationService = $validationService;
        $this->callService = $callService;
        $this->saveService = $saveService;
        $this->getService = $getService;
        $this->validationResults = [];
        $this->apiResults = [];
    }

    public function setEventVariables(array $body, string $entityName, ?string $uuid, string $componentCode)
    {
        $this->body = $body;
        $this->entityName = $entityName;
        $this->uuid = $uuid;
        $this->componentCode = $componentCode;
    }

    public function handlePost(ObjectEntity $objectEntity)
    {
        // Check if entity exists
        $entity = $this->em->getRepository("App\Entity\Entity")->findOneBy(['type' => $this->componentCode . '/' . $this->entityName]);
        if (empty($entity)) {
            return'No Entity with type ' . $this->componentCode . '/' . $this->entityName . ' exist!';
        }
        $objectEntity->setEntity($entity);

        $this->validationResults = $this->validationService->validateEntity($entity, $this->body);

        if (empty($this->validationResults)) {
            $this->callService->postEntity($entity, $this->body);
        } else {
            return [
                "message" => "validation error",
                "type" => "error",
                "path" => $entity->getName(),
                "data" => $this->validationResults,
            ];
        }

        $this->em->persist($objectEntity);
        $this->em->flush();
        return $this->apiResults;
    }

}
