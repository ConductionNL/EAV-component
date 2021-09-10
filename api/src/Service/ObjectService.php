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
    //private array $validationResults;
    //private array $apiResults;

    public function __construct(EntityManagerInterface $em, CommonGroundService $commonGroundService, ParameterBagInterface $params, ValidationService $validationService, CallService $callService, SaveService $saveService, GetService $getService)
    {
        $this->em = $em; // Why
        $this->commonGroundService = $commonGroundService; // hangt  van guzle af
        $this->params = $params;
        // Je hebt deze nodig
        $this->validationService = $validationService;
        $this->callService = $callService;
        $this->saveService = $saveService;
        // of deze, maar nooit bijde te gelijkertijd
        $this->getService = $getService;
        //$this->validationResults = [];
        //$this->apiResults = [];
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
            return [
                "message" => 'No Entity with this type exist.',
                "type" => "error",
                "path" => "url",
                "data" => ["type" => $this->componentCode . '/' . $this->entityName],
            ];
        }

        // Let get ourself our object
        if($id){
            $object =$entity = $this->em->getRepository("App\Entity\ObjectEntity")->get($id);
        }
        else{
            $object = New ObjectEntity;
            $object->setEntity($entity);
        }

        // Validation stap
        $object = $this->validationService->validateEntity($object, $this->body);

        // Let see if we have errors
        if($object->hasErrors()){
            return $this->returnErrors($object);
        }

        // Making the api calls

        // Waiting for als the guzzle the results
         Promise\Utils::settle($object->getAllPromisses)->wait();
        if($object->hasErrors()){
            return $this->returnErrors($object);
        }

        // Saving the data
        $this->em->persist($object);
        $this->em->flush();

        return $object;


        /*
        $this->validationResults = $this->validationService->validateEntity($entity, $this->body);

        if (empty($this->validationResults)) {
            $result = $this->saveService->saveEntity($entity, $this->body);
            return $this->saveService->renderResult($result);
        } else {
            return [
                "message" => "validation error",
                "type" => "error",
                "path" => $entity->getName(),
                "data" => $this->validationResults,
            ];
        }
        */
    }


    public function returnErrors(ObjectEntity $objectEntity)
    {
        return [
            "message" => "The where errors",
            "type" => "error",
            "path" => $objectEntity->getEntity()->getName(),
            "data" => $objectEntity->getAllErrors,
        ];
    }

}
