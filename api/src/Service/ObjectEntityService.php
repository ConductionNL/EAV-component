<?php

namespace App\Service;

use App\Entity\ObjectEntity;
use App\Entity\Value;
use App\Repository\ValueRepository;
use Conduction\CommonGroundBundle\Service\CommonGroundService;
use Doctrine\ORM\EntityManagerInterface;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use SensioLabs\Security\Exception\HttpException;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Paginator;
use Doctrine\Common\Collections\Collection;

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

            //TODO:set id of this $objectEntity to the $id? (If it doesnt exist already, then get and update that one?)
//            $objectEntity->setId($id);

            // Check if entity exists
            $entity = $this->em->getRepository("App\Entity\Entity")->findOneBy(['name' => $this->entityName]);
            if(empty($entity)) {
                throw new HttpException('This entity '.$this->entityName.' does not exist!', 400);
            }
            $objectEntity->setEntity($entity);

            // First get the attributes of this Entity
            $attributes = $this->em->getRepository("App\Entity\Attribute")->findBy(['entity' => $entity]);
            if (empty($attributes)) {
                throw new HttpException('This entity '.$this->entityName.' has no attributes!', 400);
            }

            // Create the uri for the values
            if(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
                $uri = "https://";
            } else {
                $uri = "http://";
            }
            $uri .= $_SERVER['HTTP_HOST'];
            // if not localhost add /api/v1 ?
            if ($_SERVER['HTTP_HOST'] != 'localhost') {
                $uri .= '/api/v1';
            }
            $uri .= '/eav/' . $this->entityName . '/' . $id;

            // Compare Post ($this->)body to the Attributes :
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
        }

        return $objectEntity;
    }

    public function handleGet($paginator)
    {
        // Check component code
        if ($this->componentCode == 'eav') {
            // Check if there is a uuid set
            if (isset($this->uuid) && $this->isValidUuid($this->uuid)) {
                $id = $this->uuid;
            } elseif (isset($this->body['id']) && $this->isValidUuid($this->body['id'])) {
                $id = $this->body['id'];
            } else {
                throw new HttpException('No valid uuid found!', 400);
            }

            // Get entity using the entity name
            $entity = $this->em->getRepository("App\Entity\Entity")->findOneBy(['name' => $this->entityName]);
            if(empty($entity)) {
                throw new HttpException('This entity '.$this->entityName.' does not exist!', 400);
            }

            // Get attributes
            $attributes = $this->em->getRepository("App\Entity\Attribute")->findBy(['entity' => $entity]);
            if (empty($attributes)) {
                throw new HttpException('This entity '.$this->entityName.' has no attributes!', 400);
            }

            // Now create the uri
            if(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
                $uri = "https://";
            } else {
                $uri = "http://";
            }
            $uri .= $_SERVER['HTTP_HOST'];
            // if not localhost add /api/v1 ?
            if ($_SERVER['HTTP_HOST'] != 'localhost') {
                $uri .= '/api/v1';
            }
            $uri .= '/eav/' . $this->entityName . '/' . $id;

            // Find the correct values
            foreach ($attributes as $attribute) {
                foreach ($attribute->getAttributeValues() as $value) {
                    if ($value->getUri() == $uri) {
                        $values[$attribute->getName()] = $value->getValue();
                    }
                }
            }

            if (!isset($values) || empty($values)) {
                throw new HttpException('No values found with this uuid '.$id, 400);
            }
            var_dump($values);
        }

        // ApiPlatform\Core\Bridge\Doctrine\Orm\Paginator
        return $paginator;
    }
}
