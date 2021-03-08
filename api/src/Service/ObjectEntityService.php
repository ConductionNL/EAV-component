<?php

namespace App\Service;

use App\Entity\Attribute;
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

    // TODO: needs a merge with handlePut function
    public function handlePost(ObjectEntity $objectEntity)
    {
        // Create a new uuid
        $id = \Ramsey\Uuid\Uuid::uuid4()->toString();

        $this->em->persist($objectEntity);
        $objectEntity->setId(Uuid::fromString($id));
        $this->em->persist($objectEntity);
        $this->em->flush();
        $objectEntity = $this->em->getRepository('App:ObjectEntity')->findOneBy(['id'=> Uuid::fromString($id)]);

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

        if ($this->componentCode != 'eav' && isset($this->body['@self'])) {
            // Get existing object with @self
            $object = $this->commonGroundService->getResource($this->body['@self']);
        } else {
            $object = [];
        }

        // Create the @id uri for the values
        $uri = $this->createUri($id);

        // Compare Post ($this->)body to the Attributes :
        $values = [];
        foreach ($this->body as $key => $bodyValue) {
            // TODO:something about this:
            if ($key == '@type' || $key == '@self') {
                continue;
            }
            $foundAttribute = false;
            foreach ($attributes as $attribute) {
                if ($attribute->getName() == $key) {
                    $foundAttribute = true;
                    // Check the value
                    $values = $this->checkValue($values, $attribute, $bodyValue);
                }
            }
            if (!$foundAttribute) {
                if ($this->componentCode == 'eav') {
                    throw new HttpException('The entity ' . $this->componentCode . '/' . $this->entityName . ' has no attribute for ' . $key . ' !', 400);
                } else {
                    $object[$key] = $bodyValue;
                }
            }
        }

        // Create the values if no errors where thrown when checking them ^
        foreach ($values as $key => $value) {
            $value = $this->saveValue($objectEntity, $value['attribute'], $value['value'], $uri);

            $values[$key] = $this->getValue($value->getAttribute(), $value);
        }

        $bodyValues = array_keys($values);
        foreach ($attributes as $attribute) {
            // If the attribute is not set in the body
            if (!in_array($attribute->getName(), $bodyValues)){
                // Check if it has a default value
                if ($attribute->getDefaultValue()) {
                    $value = $this->saveValue($objectEntity, $attribute, $attribute->getDefaultValue(), $uri);

                    $values[$attribute->getName()] = $this->getValue($attribute, $value);
                } elseif ($attribute->getNullable()) {
                    $value = $this->saveValue($objectEntity, $attribute, null, $uri);

                    $values[$attribute->getName()] = $this->getValue($attribute, $value);
                } elseif ($attribute->getRequired()){
                    throw new HttpException('The entity type: [' . $attribute->getEntity()->getType() . '] has an attribute: [' . $attribute->getName() . '] that is required!', 400);
                }
            }
        }

        // Check component code and if it is not EAV also create/update the normal object.
        if ($this->componentCode != 'eav') {
            $response = $this->commonGroundService->saveResource($object, ['component' => $this->componentCode, 'type' => $this->entityName]);
        } else {
            $response['@context'] = '/contexts/' . ucfirst($this->entityName);
        }
        $response['@id'] = $uri;
        $response['@self'] = $uri;
        $response['@type'] = ucfirst($this->entityName);
        $response['id'] = $id;

        $objectEntity->setUri($response['@id']);
        $this->em->persist($objectEntity);
        $this->em->flush();

        $response = array_merge($response, $values);

        return $response;
    }

    // TODO: needs a merge with handlePost function
    public function handlePut(ObjectEntity $objectEntity) {
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

        if (isset($this->body['@self'])) {
            // Get existing object with @self
            $object = $this->em->getRepository("App\Entity\ObjectEntity")->findOneBy(['uri' => $this->body['@self']]);
            if (empty($object)) {
                throw new HttpException('No object found with this @self: '.$this->body['@self'].' !', 400);
            }
            $objectEntity = $object;
        } else {
            // Get existing object with id
            $object = $this->em->getRepository("App\Entity\ObjectEntity")->findOneBy(['id' => $id]);
            if (empty($object)) {
                throw new HttpException('No object found with this uuid: '.$id.' !', 400);
            }
            $objectEntity = $object;
        }

        // Now create the uri for the values
        $uri = $this->createUri($id);

        // Compare Post ($this->)body to the Attributes :
        $values = [];
        $object = [];
        foreach ($this->body as $key => $bodyValue) {
            // TODO:something about this:
            if ($key == '@type' || $key == '@self') {
                continue;
            }
            $foundAttribute = false;
            foreach ($attributes as $attribute) {
                if ($attribute->getName() == $key) {
                    $foundAttribute = true;

                    // Find the correct values TODO:just get them from the $objectEntity?
                    foreach ($attribute->getAttributeValues() as $value) {
                        if ($value->getUri() == $uri) {
                            // Update the value
                            $values = $this->checkValue($values, $attribute, $bodyValue);
                        }
                    }
                }
            }
            if (!$foundAttribute) {
                if ($this->componentCode == 'eav') {
                    throw new HttpException('The entity ' . $this->componentCode . '/' . $this->entityName . ' has no attribute for ' . $key . ' !', 400);
                } else {
                    $object[$key] = $bodyValue;
                }
            }
        }

        if (!isset($values) || empty($values)) {
            throw new HttpException('No values found with this uuid '.$id, 400);
        }

        // Update the values if no errors where thrown when checking them ^
        foreach ($values as $key => $value) {
            $value = $this->saveValue($objectEntity, $value['attribute'], $value['value'], $uri);

            $values[$key] = $this->getValue($value->getAttribute(), $value);
        }

        $bodyValues = array_keys($values);
        foreach ($attributes as $attribute) {
            // If the attribute is not set in the body
            if (!in_array($attribute->getName(), $bodyValues)){
                // Check if it has a default value
                if ($attribute->getDefaultValue()) {
                    $value = $this->saveValue($objectEntity, $attribute, $attribute->getDefaultValue(), $uri);

                    $values[$attribute->getName()] = $this->getValue($attribute, $value);
                } elseif ($attribute->getNullable()) {
                    $value = $this->saveValue($objectEntity, $attribute, null, $uri);

                    $values[$attribute->getName()] = $this->getValue($attribute, $value);
                } elseif ($attribute->getRequired()){
                    throw new HttpException('The entity type: [' . $attribute->getEntity()->getType() . '] has an attribute: [' . $attribute->getName() . '] that is required!', 400);
                }
            }
        }

        // Check component code and if it is not EAV also update the normal object.
        if ($this->componentCode != 'eav') {
            $response = $this->commonGroundService->updateResource($object, $objectEntity->getUri());
        } else {
            $response['@context'] = '/contexts/' . ucfirst($this->entityName);
        }
        $response['@id'] = $uri;
        $response['@self'] = $uri;
        $response['@type'] = ucfirst($this->entityName);
        $response['id'] = $id;

        $response = array_merge($response, $values);

        return $response;
    }

    public function handleGet()
    {
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

        if (isset($this->body['@self'])) {
            // Get existing object with @self
            $object = $this->em->getRepository("App\Entity\ObjectEntity")->findOneBy(['uri' => $this->body['@self']]);
            if (empty($object)) {
                throw new HttpException('No object found with this @self: '.$this->body['@self'].' !', 400);
            }
            $objectEntity = $object;
        } else {
            // Get existing object with id
            $object = $this->em->getRepository("App\Entity\ObjectEntity")->findOneBy(['id' => $id]);
            if (empty($object)) {
                throw new HttpException('No object found with this uuid: '.$id.' !', 400);
            }
            $objectEntity = $object;
        }

        // Now create the uri
        $uri = $this->createUri($id);

        // Find the correct values
        foreach ($attributes as $attribute) {
            foreach ($attribute->getAttributeValues() as $value) {
                if ($value->getUri() == $uri) {
                    $values[$attribute->getName()] = $this->getValue($attribute, $value);
                }
            }
        }

        if (!isset($values) || empty($values)) {
            throw new HttpException('No values found with this uuid '.$id, 400);
        }

        // Check component code and if it is not EAV also create/update the normal object.
        if ($this->componentCode != 'eav') {
            $response = $this->commonGroundService->getResource($objectEntity->getUri());
        } else {
            $response['@context'] = '/contexts/' . ucfirst($this->entityName);
        }
        $response['@id'] = $uri;
        $response['@self'] = $uri;
        $response['@type'] = ucfirst($this->entityName);
        $response['id'] = $id;

        $response = array_merge($response, $values);

        return $response;
    }

    private function checkValue($values, Attribute $attribute, $bodyValue)
    {
        $exceptionMessage = '';

        $typeFormat = $attribute->getType() . '-' . $attribute->getFormat();
        switch ($typeFormat) {
            case 'string-string':
                if ($attribute->getEnum()) {
                    $inEnum = false;
                    foreach ($attribute->getEnum() as $enumValue) {
                        if ($enumValue == $bodyValue){
                            $inEnum = true;
                            break;
                        }
                    }
                    if (!$inEnum) {
                        $exceptionMessage ='Attribute: [' . $attribute->getName() . '] must be one of the following values: [' . implode( ", ", $attribute->getEnum() ) . '] !';
                    }
                }
                break;
            case 'number-number': //TODO: 'number' is probably for numbers other than integer?
            case 'boolean-boolean':
            case 'array-array':
            case 'integer-integer':
                break;
            default:
                $exceptionMessage = 'The entity type: [' . $attribute->getEntity()->getType() . '] has an attribute: [' . $attribute->getName() . '] with an unknown type-format combination: [' . $typeFormat . '] !';
        }
        if ($exceptionMessage != ''){
            throw new HttpException($exceptionMessage, 400);
        }
        $values[$attribute->getName()] = ['attribute'=>$attribute, 'value'=>$bodyValue];
        return $values;
    }

    private function saveValue(ObjectEntity $objectEntity, Attribute $attribute, $bodyValue, $uri)
    {
        $value = new Value();
        $value->setObjectEntity($objectEntity);
        $value->setAttribute($attribute);
        $value->setUri($uri);

        $typeFormat = $attribute->getType() . '-' . $attribute->getFormat();
        switch ($typeFormat) {
            case 'string-string':
                $value->setValue($bodyValue);
                break;
            case 'number-number': //TODO: 'number' is probably for numbers other than integer?
            case 'integer-integer':
                $value->setIntegerValue($bodyValue);
                break;
            case 'boolean-boolean':
                $value->setBooleanValue($bodyValue);
                break;
            case 'array-array':
                $value->setArrayValue($bodyValue);
                break;
        }

        $this->em->persist($value);
        $this->em->flush();
        return $value;
    }

    private function getValue(Attribute $attribute, Value $value)
    {
        $typeFormat = $attribute->getType() . '-' . $attribute->getFormat();
        switch ($typeFormat) {
            case 'string-string':
                return $value->getValue();
            case 'number-number': //TODO: 'number' is probably for numbers other than integer?
            case 'integer-integer':
                return $value->getIntegerValue();
            case 'boolean-boolean':
                return $value->getBooleanValue();
            case 'array-array':
                return $value->getArrayValue();
            default:
                throw new HttpException('The entity type: [' . $attribute->getEntity()->getType() . '] has an attribute: [' . $attribute->getName() . '] with an unknown type-format combination: [' . $typeFormat . '] !', 400);
        }
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
        return $uri . '/object_entities/' . $this->componentCode . '/' . $this->entityName . '/' . $id;
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
