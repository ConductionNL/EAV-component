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

    public function handlePost(ObjectEntity $objectEntity)
    {
        // If there is a uuid set
        if (isset($this->uuid) && $this->isValidUuid($this->uuid)) {
            $id = $this->uuid;
        } else {
            // Create a new uuid
            $id = \Ramsey\Uuid\Uuid::uuid4()->toString();
        }

        //TODO:set id of this $objectEntity to the $id? (If it doesnt exist already, then get and update that one?)
//        $objectEntity->setId(Uuid::fromString($id));

        // Check if entity exists
        $entity = $this->em->getRepository("App\Entity\Entity")->findOneBy(['type' => $this->componentCode . '/' . $this->entityName]);
        if(empty($entity)) {
            throw new HttpException('No Entity with type ' . $this->componentCode . '/' . $this->entityName . ' exist!', 400);
        }
        $objectEntity->setEntity($entity);

        // First get the attributes of this Entity
        $attributes = $this->em->getRepository("App\Entity\Attribute")->findBy(['entity' => $entity]);
        if (empty($attributes)) {
            throw new HttpException('This entity '.$this->componentCode . '/' . $this->entityName.' has no attributes!', 400);
        }

        // Create the uri for the values
        $uri = $this->createUri($id);

        // Compare Post ($this->)body to the Attributes :
        $values = [];
        foreach ($this->body as $key => $bodyValue) {
            if ($key == '@type' || $key == '@self') {
                continue;
            }
            $foundAttribute = false;
            foreach ($attributes as $attribute) {
                if ($attribute->getName() == $key) {
                    $foundAttribute = true;
                    // Create the value
                    $value = new Value();
                    $value->setUri($uri);
                    $value->setValue($bodyValue);
                    $value->setAttribute($attribute);
                    $value->setObjectEntity($objectEntity);
                    $this->em->persist($value);
                    $this->em->flush();

                    $values[$key] = $bodyValue;
                }
            }
            if (!$foundAttribute and $this->componentCode == 'eav') {
                throw new HttpException('The entity ' . $this->componentCode . '/' . $this->entityName . ' has no attribute for ' . $key . ' !', 400);
            }
        }

        // Check component code and if it is not EAV also create/update the normal object.
        if ($this->componentCode != 'eav') {
            // TODO:What to do with id?
//            $this->commonGroundService->saveResource($object, ['component' => $this->componentCode, 'type' => $this->entityName]);
        }

        $response['@context'] = '/contexts/' . ucfirst($this->entityName);
        $response['@id'] = '/' . $this->pluralize($this->entityName) .  '/' . $id;
        $response['@type'] = ucfirst($this->entityName);
        $response['id'] = $id;
        $response = array_merge($response, $values);

        return $response;
    }

    public function handleGet()
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

            // Get entity using the entity name as type
            $entity = $this->em->getRepository("App\Entity\Entity")->findOneBy(['type' => $this->componentCode . '/' . $this->entityName]);
            if(empty($entity)) {
                throw new HttpException('No Entity with type ' . $this->componentCode . '/' . $this->entityName . ' exist!', 400);
            }

            // Get attributes
            $attributes = $this->em->getRepository("App\Entity\Attribute")->findBy(['entity' => $entity]);
            if (empty($attributes)) {
                throw new HttpException('This entity '.$this->componentCode . '/' . $this->entityName.' has no attributes!', 400);
            }

            // Now create the uri
            $uri = $this->createUri($id);

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
        }

        $response['@context'] = '/contexts/' . ucfirst($this->entityName);
        $response['@id'] = '/' . $this->pluralize($this->entityName) .  '/' . $id;
        $response['@type'] = ucfirst($this->entityName);
        $response['id'] = $id;
        $response = array_merge($response, $values);

        return $response;
    }

    private function createUri($id)
    {
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
        return $uri . '/' . $this->componentCode . '/' . $this->entityName . '/' . $id;
    }

    /**
     * Pluralizes a word.
     *
     * @param string $singular Singular form of word
     * @return string Pluralized word
     */
    private function pluralize($singular) {
        $last_letter = strtolower($singular[strlen($singular)-1]);
        switch($last_letter) {
            case 'y':
                return substr($singular,0,-1).'ies';
            case 's':
                return $singular.'es';
            default:
                return $singular.'s';
        }
    }

    /**
     * Check if a given string is a valid UUID
     *
     * @param   string  $uuid   The string to check
     * @return  boolean
     */
    private function isValidUuid( $uuid ) {
        if (!is_string($uuid) || (preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/', $uuid) !== 1)) {
            return false;
        }

        return true;
    }
}
