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

    public function setEventVariables($body, $entityName, $uuid, $componentCode)
    {
        $this->body = $body;
        $this->entityName = $entityName;
        $this->uuid = $uuid;
        $this->componentCode = $componentCode;
    }

    // TODO: needs a merge with handlePut function
    public function handlePost(ObjectEntity $objectEntity)
    {
        // Get the uuid
        $id = $objectEntity->getId();

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
            // Get existing extern object with @self
            $externObject = $this->commonGroundService->getResource($this->body['@self']);
            $object['@id'] = $externObject['@id'];
        } else {
            $object = [];
        }

        // Create the @id uri for the values
        $uri = $this->createUri($id);

        // Compare Post ($this->)body to the Attributes :
        $values = [];
        foreach ($this->body as $key => $bodyValue) {
            // TODO:something about this:
            if ($key == '@self') {
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
                } else {
                    // also show not set values as null in the response
                    $value = $this->saveValue($objectEntity, $attribute, null, $uri);

                    $values[$attribute->getName()] = $this->getValue($attribute, $value);
                }
            }
        }

        // Check component code and if it is not EAV also create/update the normal object.
        if ($this->componentCode != 'eav') {
            $response = $this->commonGroundService->saveResource($object, ['component' => $this->componentCode, 'type' => $this->entityName]);
            $response['@self'] = $response['@id'];
            $response['@eav'] = $uri;
            $response['@eavType'] = ucfirst($this->entityName);
            $response['eavId'] = $id;
        } else {
            $response['@context'] = '/contexts/' . ucfirst($this->entityName);
            $response['@id'] = $uri;
            $response['@type'] = ucfirst($this->entityName);
            $response['id'] = $id;
            $response['@self'] = $uri;
            $response['@eav'] = $response['@id'];
            $response['@eavType'] = $response['@type'];
            $response['eavId'] = $response['id'];
        }
        $objectEntity->setUri($response['@id']);
        $this->em->persist($objectEntity);
        $this->em->flush();

        $response = array_merge($response, $values);

        return $response;
    }

    // TODO: needs a merge with handlePost function
    public function handlePut() {
        // Check if there is a uuid set
        if (isset($this->uuid) && $this->isValidUuid($this->uuid)) {
            $id = $this->uuid;
        } elseif (!isset($this->body['@self'])) {
            throw new HttpException('No @self or valid uuid found!', 400);
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
        $id = $objectEntity->getId(); // important!

        // Now create the uri for the values
        $uri = $this->createUri($id);

        // Compare Post ($this->)body to the Attributes :
        $values = []; // !
        $object = []; // !
        foreach ($this->body as $key => $bodyValue) {
            // TODO:something about this:
            if ($key == '@self') {
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
                            $values = $this->checkValue($values, $attribute, $bodyValue, $value);
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

        // Update the values if no errors where thrown when checking them ^
        foreach ($values as $key => $value) {
            $value = $this->saveValue($objectEntity, $value['attribute'], $value['value'], $uri, $value['valueObject']);

            $values[$key] = $this->getValue($value->getAttribute(), $value);
        }

        // also show not changed values in the response body
        $bodyValues = array_keys($values);
        foreach ($attributes as $attribute) {
            // If the attribute is not set in the body
            if (!in_array($attribute->getName(), $bodyValues)){
                foreach ($attribute->getAttributeValues() as $value) {
                    if ($value->getUri() == $uri) {
                        $values[$attribute->getName()] = $this->getValue($attribute, $value);
                    }
                }
            }
        }

        // Check component code and if it is not EAV also update the normal object.
        if ($this->componentCode != 'eav') {
            $response = $this->commonGroundService->updateResource($object, $objectEntity->getUri());
            $response['@self'] = $response['@id'];
            $response['@eav'] = $uri;
            $response['@eavType'] = ucfirst($this->entityName);
            $response['eavId'] = $id;
        } else {
            $response['@context'] = '/contexts/' . ucfirst($this->entityName);
            $response['@id'] = $uri;
            $response['@type'] = ucfirst($this->entityName);
            $response['id'] = $id;
            $response['@self'] = $uri;
            $response['@eav'] = $response['@id'];
            $response['@eavType'] = $response['@type'];
            $response['eavId'] = $response['id'];
        }

        $response = array_merge($response, $values);

        return $response;
    }

    public function handleGet()
    {
        // Check if there is a uuid set
        if (isset($this->uuid) && $this->isValidUuid($this->uuid)) {
            $id = $this->uuid;
        } elseif (!isset($this->body['@self'])) {
            throw new HttpException('No @self or valid uuid found!', 400);
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
        $id = $objectEntity->getId(); // important!

        // Now create the uri
        $uri = $this->createUri($id);

        // Find the correct values TODO:just get them from the $objectEntity?
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

        // Check component code and if it is not EAV also get the normal object.
        if ($this->componentCode != 'eav') {
            $response = $this->commonGroundService->getResource($objectEntity->getUri());
            $response['@self'] = $response['@id'];
            $response['@eav'] = $uri;
            $response['@eavType'] = ucfirst($this->entityName);
            $response['eavId'] = $id;
        } else {
            $response['@context'] = '/contexts/' . ucfirst($this->entityName);
            $response['@id'] = $uri;
            $response['@type'] = ucfirst($this->entityName);
            $response['id'] = $id;
            $response['@self'] = $uri;
            $response['@eav'] = $response['@id'];
            $response['@eavType'] = $response['@type'];
            $response['eavId'] = $response['id'];
        }

        $response = array_merge($response, $values);

        return $response;
    }

    private function checkValue($values, Attribute $attribute, $bodyValue, Value $valueObject = null)
    {
        // Get attribute type and format
        $typeFormat = $attribute->getType() . '-' . $attribute->getFormat();

        // Check if attribute has an enum and if so check if the bodyValue equals one of the enumValues
        if ($attribute->getEnum() && !in_array($bodyValue, $attribute->getEnum())) {
            if ($typeFormat == 'array-array' || $typeFormat == 'boolean-boolean'){
                $enumValues = json_encode($attribute->getEnum());
            } else {
                $enumValues = '[' . implode( ", ", $attribute->getEnum() ) . ']';
            }
            throw new HttpException('Attribute: [' . $attribute->getName() . '] must be one of the following values: ' . $enumValues . ' !', 400);
        }

        // Check if the value is null and if so if this is allowed or not
        if (!isset($bodyValue)) {
            if (!$attribute->getNullable()) {
                throw new HttpException('Attribute: [' . $attribute->getName() . '] expects ' . $attribute->getType() . ', ' . gettype($bodyValue) . ' given!', 400);
            }
        }

        // Do checks for attribute depending on its type-format
        switch ($typeFormat) {
            case 'string-string':
                if (!is_string($bodyValue)) {
                    throw new HttpException('Attribute: [' . $attribute->getName() . '] expects ' . $attribute->getType() . ', ' . gettype($bodyValue) . ' given!', 400);
                }
                if ($attribute->getMinLength() && strlen($bodyValue) < $attribute->getMinLength()) {
                    throw new HttpException('Attribute: [' . $attribute->getName() . '] is to short, minimum length is ' . $attribute->getMinLength() . ' !', 400);
                }
                if ($attribute->getMaxLength() && strlen($bodyValue) > $attribute->getMaxLength()) {
                    throw new HttpException('Attribute: [' . $attribute->getName() . '] is to long, maximum length is ' . $attribute->getMaxLength() . ' !', 400);
                }
                break;
            case 'number-number':
                if (!is_integer($bodyValue) && !is_float($bodyValue) && gettype($bodyValue) != 'float' && gettype($bodyValue) != 'double') {
                    throw new HttpException('Attribute: [' . $attribute->getName() . '] expects ' . $attribute->getType() . ', ' . gettype($bodyValue) . ' given!', 400);
                }
                break;
            case 'integer-integer':
                if (!is_integer($bodyValue)) {
                    throw new HttpException('Attribute: [' . $attribute->getName() . '] expects ' . $attribute->getType() . ', ' . gettype($bodyValue) . ' given!', 400);
                }
                if ($attribute->getMinimum()) {
                    if ($attribute->getExclusiveMinimum() && $bodyValue <= $attribute->getMinimum()) {
                        throw new HttpException('Attribute: [' . $attribute->getName() . '] must be higher than ' . $attribute->getMinimum() . ' !', 400);
                    } elseif ($bodyValue < $attribute->getMinimum()) {
                        throw new HttpException('Attribute: [' . $attribute->getName() . '] must be ' . $attribute->getMinimum() . ' or higher!', 400);
                    }
                }
                if ($attribute->getMaximum()) {
                    if ($attribute->getExclusiveMaximum() && $bodyValue >= $attribute->getMaximum()) {
                        throw new HttpException('Attribute: [' . $attribute->getName() . '] must be lower than ' . $attribute->getMaximum() . ' !', 400);
                    } elseif ($bodyValue > $attribute->getMaximum()) {
                        throw new HttpException('Attribute: [' . $attribute->getName() . '] must be ' . $attribute->getMaximum() . ' or lower!', 400);
                    }
                }
                if ($attribute->getMultipleOf() && $bodyValue % $attribute->getMultipleOf() != 0) {
                    throw new HttpException('Attribute: [' . $attribute->getName() . '] must be a multiple of ' . $attribute->getMultipleOf() . ', ' . $bodyValue . ' is not a multiple of ' . $attribute->getMultipleOf() . ' !', 400);
                }
                break;
            case 'boolean-boolean':
                if (!is_bool($bodyValue)) {
                    throw new HttpException('Attribute: [' . $attribute->getName() . '] expects ' . $attribute->getType() . ', ' . gettype($bodyValue) . ' given!', 400);
                }
                break;
            case 'array-array':
                if (!is_array($bodyValue)) {
                    throw new HttpException('Attribute: [' . $attribute->getName() . '] expects ' . $attribute->getType() . ', ' . gettype($bodyValue) . ' given!', 400);
                }
                if ($attribute->getMinItems() && count($bodyValue) < $attribute->getMinItems()) {
                    throw new HttpException('Attribute: [' . $attribute->getName() . '] has to few items ( ' . count($bodyValue) . ' ), the minimum array length of this attribute is ' . $attribute->getMinItems() . ' !', 400);
                }
                if ($attribute->getMaxItems() && count($bodyValue) > $attribute->getMaxItems()) {
                    throw new HttpException('Attribute: [' . $attribute->getName() . '] has to many items ( ' . count($bodyValue) . ' ), the maximum array length of this attribute is ' . $attribute->getMaxItems() . ' !', 400);
                }
                if ($attribute->getUniqueItems() && count(array_filter(array_keys($bodyValue), 'is_string')) == 0) {
                    // TODO:check this in another way so all kinds of arrays work with it.
                    $containsStringKey = false;
                    foreach ($bodyValue as $arrayItem) {
                        if (is_array($arrayItem) && count(array_filter(array_keys($arrayItem), 'is_string')) > 0){
                            $containsStringKey = true; break;
                        }
                    }
                    if (!$containsStringKey && count($bodyValue) !== count(array_unique($bodyValue))) {
                        throw new HttpException('Attribute: [' . $attribute->getName() . '] must be an array of unique items!', 400);
                    }
                }
                break;
            case 'datetime-datetime':
                break;
            default:
                throw new HttpException('The entity type: [' . $attribute->getEntity()->getType() . '] has an attribute: [' . $attribute->getName() . '] with an unknown type-format combination: [' . $typeFormat . '] !', 400);
        }

        $values[$attribute->getName()] = ['attribute'=>$attribute, 'value'=>$bodyValue];
        if (isset($valueObject)){
            $values[$attribute->getName()]['valueObject'] = $valueObject;
        }
        return $values;
    }

    private function saveValue(ObjectEntity $objectEntity, Attribute $attribute, $bodyValue, $uri, Value $valueObject = null)
    {
        if (isset($valueObject)){
            $value = $valueObject;
        } else {
            $value = new Value();
        }
        $value->setObjectEntity($objectEntity);
        $value->setAttribute($attribute);
        $value->setUri($uri);

        // If the attribute is nullable just set no value so it is null
        if (!is_null($bodyValue)) {
            // Get attribute type and format
            $typeFormat = $attribute->getType() . '-' . $attribute->getFormat();
            switch ($typeFormat) {
                case 'string-string':
                    $value->setValue($bodyValue);
                    break;
                case 'number-number':
                    $value->setNumberValue($bodyValue);
                case 'integer-integer':
                    $value->setIntegerValue($bodyValue);
                    break;
                case 'boolean-boolean':
                    if (is_string($bodyValue)) {
                        // This is used for defaultValue, this is always a string type instead of a boolean
                        $bodyValue = $bodyValue === 'true';
                    }
                    $value->setBooleanValue($bodyValue);
                    break;
                case 'array-array':
                    if (is_string($bodyValue)) {
                        $bodyValue = $this->createArrayFromString($bodyValue);
                    }
                    $value->setArrayValue($bodyValue);
                    break;
                case 'datetime-datetime':
                    $value->setDateTimeValue(new \DateTime($bodyValue));
                    break;
            }
        }

        $this->em->persist($value);
        $this->em->flush();
        return $value;
    }

    private function getValue(Attribute $attribute, Value $value)
    {
        // Get attribute type and format
        $typeFormat = $attribute->getType() . '-' . $attribute->getFormat();
        switch ($typeFormat) {
            case 'string-string':
                return $value->getValue();
            case 'number-number':
                return $value->getNumberValue();
            case 'integer-integer':
                return $value->getIntegerValue();
            case 'boolean-boolean':
                return $value->getBooleanValue();
            case 'array-array':
                return $value->getArrayValue();
            case 'datetime-datetime':
                $datetime = $value->getDateTimeValue();
                if (!empty($datetime)){
                    $datetime = $datetime->format('Y-m-d\TH:i:sP');
                }
                return $datetime;
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
            $uri .= '/api/v1/eav';
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

    private function createArrayFromString($bodyValue){
        if (is_string($bodyValue) ) {
            if (strpos($bodyValue, ";")){
                $bodyValue = explode(";", $bodyValue);
                foreach ($bodyValue as &$object) {
                    $object = $this->createArrayFromString($object);
                }
            } else {
                $bodyValue = explode(",", $bodyValue);
                foreach ($bodyValue as $key => $keyValue) {
                    if (strpos($keyValue, ":")){
                        $keyValue = explode(":",$keyValue);
                        unset($bodyValue[$key]);
                        $bodyValue[$keyValue[0]] = $keyValue[1];
                    }
                }
            }
        }
        return $bodyValue;
    }
}
