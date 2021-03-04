<?php

namespace App\Service;

use App\Entity\ObjectEntity;
use App\Entity\Value;
use Conduction\CommonGroundBundle\Service\CommonGroundService;
use Doctrine\ORM\EntityManagerInterface;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class ObjectEntityService
{
    private $em;
    private $commonGroundService;
    private $params;
    private $componentCode;
    private $entityName;
    private $uuid;
    private $body;

    public function __construct(EntityManagerInterface $em, CommonGroundService $commonGroundService, ParameterBagInterface $params)
    {
        $this->em = $em;
        $this->commonGroundService = $commonGroundService;
        $this->params = $params;
    }

    public function setEventVariables($componentCode, $entityName, $uuid, $body)
    {
        $this->componentCode = $componentCode;
        $this->entityName = $entityName;
        $this->uuid = $uuid;
        $this->body = $body;
    }

    /**
     * Check if a given string is a valid UUID
     *
     * @param   string  $uuid   The string to check
     * @return  boolean
     */
    function isValidUuid( $uuid ) {
        if (!is_string($uuid) || (preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/', $uuid) !== 1)) {
            return false;
        }

        return true;
    }

    public function handlePost(ObjectEntity $objectEntity)
    {
        // Check component code
        if ($this->componentCode == 'eav') {
            // If there is a uuid set
            if (isset($this->uuid) && $this->isValidUuid($this->uuid)) {
                $id = $this->uuid;
            } else {
                // Create a new uuid
                $id = \Ramsey\Uuid\Uuid::uuid4()->toString();
            }

            // Check if entity exists
            $entity = $this->em->getRepository("App\Entity\Entity")->findOneBy(['name' => $this->entityName]);
            if(!empty($entity)) {
                $objectEntity->setEntity($entity);
            } else {
                //TODO:error handling
                var_dump('This entity '.$this->entityName.' does not exist');
            }

            // Compare Post ($this->)body to the Attributes of this Entity^ :
            $attributes = $this->em->getRepository("App\Entity\Attribute")->findBy(['entity' => $entity]);
            if (!empty($attributes)) {
                // Create the uri for the values
                $uri = 'localhost/eav/'.$this->entityName.'/'.$id; // TODO:should be domain/api/v1/eav/ , get host function

                foreach ($this->body as $key => $bodyValue) {
                    if ($key == '@type' || $key == '@self') {
                        continue;
                    }
                    foreach ($attributes as $attribute) {
                        if ($attribute->getName() == $key) {
                            // Create the value
                            $value = new Value();
                            $value->setUri($uri);
                            $value->setValue($bodyValue);
                            $value->setAttribute($attribute);
                            $value->setObjectEntity($objectEntity);
                            $this->em->persist($value);
                            $this->em->flush();
                        }
                    }
                }
            } else {
                //TODO:error handling
                var_dump('This entity '.$this->entityName.' has no attributes');
            }
        }

        return $objectEntity;
    }

    public function handleGet(ObjectEntity $objectEntity)
    {
        // Check component code
        if ($this->componentCode == 'eav') {

        }

        // Get entity using the entity name
//        $this->entityName;

        // Get attributes

        return $objectEntity;
    }
}
