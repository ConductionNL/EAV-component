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
    private string $componentCode;
    private string $entityName;
    private ?string $uuid;
    private array $body;
    private array $validationResults;
    private array $apiResults;

    public function __construct(EntityManagerInterface $em, CommonGroundService $commonGroundService, ParameterBagInterface $params, ValidationService $validationService)
    {
        $this->em = $em;
        $this->commonGroundService = $commonGroundService;
        $this->params = $params;
        $this->validationService = $validationService;
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

        $this->validationResults = $this->validationService->validateEntity($entity, $this->body);

        if (empty($this->validationResults)) {
            var_dump('no errors, now we should post');
            // TODO actually post with new post service
        }

        return $this->validationResults;
    }

}
