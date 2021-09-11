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

class ValidationService
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /*@todo docs */
    public function validateEntity (ObjectEntity $objectEntity, array $post) {

        $entity = $objectEntity->getEntity();
        foreach($entity->getAttributes() as $attribute) {

            // check if we have a value to validate
            if(key_exists($attribute->getName(), $post)){
                // Lets see if it is an array of objects
                if(!$attribute->getMultiple() || $attribute->getType() != 'object') {
                    if (!$attribute->getMultiple() && is_array($post[$attribute->getName()]) && $attribute->getType() != 'object') {
                        $objectEntity->addError($attribute->getName(),'Multiple is not set for this value.');
                        continue;
                    } //TODO same thing the other way around, must be array if multiple. maybe move this to validateAttribute:
                    $objectEntity = $this->validateAttribute($objectEntity, $attribute, $post[$attribute->getName()]);
                }
                // Damnit, an array. We will need to loop :(
                else {
                    foreach($post[$attribute->getName()] as $row) {
                        if ($attribute->getMultiple() && !is_array($row)) {
                            $objectEntity->addError($attribute->getName(),'Multiple is set for this value. Expecting an array.');
                            break;
                        }
                        $value = $objectEntity->getValueByAttribute($attribute);
                        if(array_key_exists('id', $row)) {
                            $subObject = $objectEntity->getValueByAttribute($attribute)->getObjects()->get($row['id']);
                        }
                        else {
                            $subObject = New ObjectEntity();
                            $subObject->setSubresourceOf($value);
                            $subObject->setEntity($attribute->getObject());
                        }
                        $subObject = $this->validateEntity($subObject, $row);
                        // We need to persist if this is a new ObjectEntity in order to set and getId to generate the uri...
                        $this->em->persist($subObject);
                        $subObject->setUri($this->createUri($subObject->getEntity()->getType(), $subObject->getId()));
                        // if not we can set the value
                        if (!$subObject->getHasErrors()) {
                            $subObject->getValueByAttribute($attribute)->setValue($subObject);
                            $value->addObject($subObject);
                        }
                    }
                }
            }
            // TODO: something with defaultValue, maybe not here? (but do check if defaultValue is set before returning this is required!)
//            elseif ($attribute->getDefaultValue()) {
//                $post[$attribute->getName()] = $attribute->getDefaultValue();
//            }
            // TODO: something with nullable, maybe not here? (but do check if nullable is set before returning this is required!)
//            elseif ($attribute->getNullable()) {
//                $post[$attribute->getName()] = null;
//            }
            // its not there but should it be?
            elseif($attribute->getRequired()){
                $objectEntity->addError($attribute->getName(),'this attribute is required');
            } else {
                /* @todo handling the setting to null of exisiting variables */
                $objectEntity->getValueByAttribute($attribute)->setValue(null);
            }

            /* @todo dit is de plek waarop we weten of er een appi call moet worden gemaakt */

            /*
            if(!$objectEntity->hasErrors() && VASTTELLEN OF DIE EXERN MOET){
                // $objectEntity->addPromise($this->createPromise($objectEntity, $post));
            }
            */
        }

        return $objectEntity;
    }

    /*
     * Returns a Value on succes or a false on failure
     * @todo docs */
    private function validateAttribute(ObjectEntity $objectEntity, Attribute $attribute, $value) {



        $attributeType = $attribute->getType();

        // Do validation for attribute depending on its type
        switch ($attributeType) {
            case 'object':
                // lets see if we already have a sub object
                $valueObject = $objectEntity->getValueByAttribute($attribute);

                // Lets see if the object already exists
                if(!$valueObject->getValue()){
                    $subObject = New ObjectEntity();
                    $subObject->setEntity($attribute->getObject());
                    $valueObject->setValue($subObject);
                } else {
                    $subObject = $valueObject->getValue();
                }

                // TODO: more validation for type object?
                $subObject = $this->validateEntity($subObject, $value);

                // We need to persist if this is a new ObjectEntity in order to set and getId to generate the uri...
                $this->em->persist($subObject);
                $subObject->setUri($this->createUri($subObject->getEntity()->getType(), $subObject->getId()));

                // Push it into our object
                $value = $subObject;
                break;
            case 'string':
                if (!$attribute->getMultiple() && !is_string($value)) {
                    $objectEntity->addError($attribute->getName(),'Expects ' . $attribute->getType() . ', ' . gettype($value) . ' given.');
                }
                if ($attribute->getMultiple() && !is_array($value)) {
                    $objectEntity->addError($attribute->getName(),'Expects array, ' . gettype($value) . ' given.');
                }
                if ($attribute->getMinLength() && strlen($value) < $attribute->getMinLength()) {
                    $objectEntity->addError($attribute->getName(),'Is to short, minimum length is ' . $attribute->getMinLength() . '.');
                }
                if ($attribute->getMaxLength() && strlen($value) > $attribute->getMaxLength()) {
                    $objectEntity->addError($attribute->getName(),'Is to long, maximum length is ' . $attribute->getMaxLength() . '.');
                }
                break;
            case 'number':
                if (!$attribute->getMultiple() && !is_integer($value) && !is_float($value) && gettype($value) != 'float' && gettype($value) != 'double') {
                    $objectEntity->addError($attribute->getName(),'Expects ' . $attribute->getType() . ', ' . gettype($value) . ' given.');
                }
                if (!is_array($value) && $attribute->getMultiple()) {
                    $objectEntity->addError($attribute->getName(),'Expects array, ' . gettype($value) . ' given.');
                }
                break;
            case 'integer':
                if (!$attribute->getMultiple() && !is_integer($value)) {
                    $objectEntity->addError($attribute->getName(),'Expects ' . $attribute->getType() . ', ' . gettype($value) . ' given.');
                }
                if ($attribute->getMultiple() && !is_array($value)) {
                    $objectEntity->addError($attribute->getName(),'Expects array, ' . gettype($value) . ' given.');
                }
                if ($attribute->getMinimum()) {
                    if ($attribute->getExclusiveMinimum() && $value <= $attribute->getMinimum()) {
                        $objectEntity->addError($attribute->getName(),'Must be higher than ' . $attribute->getMinimum() . '.');
                    } elseif ($value < $attribute->getMinimum()) {
                        $objectEntity->addError($attribute->getName(),'Must be ' . $attribute->getMinimum() . ' or higher.');
                    }
                }
                if ($attribute->getMaximum()) {
                    if ($attribute->getExclusiveMaximum() && $value >= $attribute->getMaximum()) {
                        $objectEntity->addError($attribute->getName(),'Must be lower than ' . $attribute->getMaximum() . '.');
                    } elseif ($value > $attribute->getMaximum()) {
                        $objectEntity->addError($attribute->getName(),'Must be ' . $attribute->getMaximum() . ' or lower.');
                    }
                }
                if ($attribute->getMultipleOf() && $value % $attribute->getMultipleOf() != 0) {
                    $objectEntity->addError($attribute->getName(),'Must be a multiple of ' . $attribute->getMultipleOf() . ', ' . $value . ' is not a multiple of ' . $attribute->getMultipleOf() . '.');
                }
                break;
            case 'boolean':
                if (!$attribute->getMultiple() && !is_bool($value)) {
                    $objectEntity->addError($attribute->getName(),'Expects ' . $attribute->getType() . ', ' . gettype($value) . ' given.');
                }
                if ($attribute->getMultiple() && !is_array($value)) {
                    $objectEntity->addError($attribute->getName(),'Expects array, ' . gettype($value) . ' given.');
                }
                break;
            // TODO: move these validations to validateEntity where array/multiple is checked
//            case 'array':
//                if (!is_array($value)) {
//                    $objectEntity->addError($attribute->getName(),'Expects ' . $attribute->getType() . ', ' . gettype($value) . ' given.');
//                }
//                if ($attribute->getMinItems() && count($value) < $attribute->getMinItems()) {
//                    $objectEntity->addError($attribute->getName(),'The minimum array length of this attribute is ' . $attribute->getMinItems() . '.');
//                }
//                if ($attribute->getMaxItems() && count($value) > $attribute->getMaxItems()) {
//                    $objectEntity->addError($attribute->getName(),'The maximum array length of this attribute is ' . $attribute->getMaxItems() . '.');
//                }
//                if ($attribute->getUniqueItems() && count(array_filter(array_keys($value), 'is_string')) == 0) {
//                    // TODOmaybe:check this in another way so all kinds of arrays work with it.
//                    $containsStringKey = false;
//                    foreach ($value as $arrayItem) {
//                        if (is_array($arrayItem) && count(array_filter(array_keys($arrayItem), 'is_string')) > 0){
//                            $containsStringKey = true; break;
//                        }
//                    }
//                    if (!$containsStringKey && count($value) !== count(array_unique($value))) {
//                        $objectEntity->addError($attribute->getName(),'Must be an array of unique items');
//                    }
//                }
//                break;
            case 'datetime':
                if (!$attribute->getMultiple()) {
                    try {
                        new \DateTime($value);
                    } catch (HttpException $e) {
                        $objectEntity->addError($attribute->getName(),'Expects ' . $attribute->getType() . ', failed to parse string to DateTime.');
                    }
                }
                if ($attribute->getMultiple() && !is_array($value)) {
                    $objectEntity->addError($attribute->getName(),'Expects array, ' . gettype($value) . ' given.');
                }
                break;
            default:
                $objectEntity->addError($attribute->getName(),'has an an unknown type: [' . $attributeType . ']');
        }

        // if not we can set the value
        if (!$objectEntity->getHasErrors()) {
            $objectEntity->getValueByAttribute($attribute)->setValue($value);
        }

        return $objectEntity;
    }


    function createPromise(ObjectEntity $objectEntity, array $post){
        //TODO: client is not recognized, so commented out for now

        // We willen de post wel opschonnen, met andere woorden alleen die dingen posten die niet als in een atrubte zijn gevangen



        // Async aanroepen van de promise methode in cg bundel
//        $promise = $client->requestAsync('GET', 'http://httpbin.org/get', $post);
//
//        // Creating a promise
//        $promise->then(
//            function (ResponseInterface $response, ObjectEntity $objectEntity) {
//                $object = json_decode($response->getBody()->getContents(), true);
//                $objectEntity->setUri($object['@id']);
//            },
//            function (RequestException $e, ObjectEntity $objectEntity) {
//                $objectEntity->addError($objectEntity->name,$e->getMessage());
//            }
//        );
//
//        return $promise;
    }

    //TODO: change this to work better? (known to cause problems) used it to generate the @id / @eav for eav objects (intern and extern objects).
    public function createUri($type, $id)
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
        return $uri . '/object_entities/' . $type . '/' . $id;
    }
}
