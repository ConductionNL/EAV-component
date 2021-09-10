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


    /*@todo docs */
    public function validateEntity (ObjectEntity $objectEntity, array $post) {

        $entity = $objectEntity->getEntity();
        foreach($entity->getAttributes() as $attribute){

            // check if we have a value to validate
            if(key_exists($attribute->getName(), $post)){
                $objectEntity = $this->validateAttribute($objectEntity, $attribute, $post[$attribute->getName()]);
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
            }

            /* @todo handling the setting to null of exisiting variables */

            /* @todo dit is de plek waarop we weten of er een appi call moet worden gemaakt */

            /*
            if(!$objectEntity->hasErrors() && VASTTELLEN OF DIE EXERN MOET){
                // $objectEntity->addPromise($this->createPromise($objectEntity, $post));
            }
            */
        }

        return $objectEntity;
    }

    /**
     * Check if array is associative
     *
     * @param array $arr
     * @return bool
     */
    function isAssoc(array $arr)
    {
        if (array() === $arr) return false;
        return array_keys($arr) !== range(0, count($arr) - 1);
    }

    /*
     * Returns a Value on succes or a false on failure
     * @todo docs */
    private function validateAttribute(ObjectEntity $objectEntity, Attribute $attribute, $value) {

        $attributeType = $attribute->getType();

        $result = '';

        // Do validation for attribute depending on its type
        switch ($attributeType) {
            case 'object':
                // lets see if we already have an sub object
                $valueObject = $objectEntity->getValueByAttribute($attribute);

                // Lets see if the object already exisit
                if(!$valueObject->getObject()){
                    $subObject = New ObjectEntity();
                    $subObject->setEntity($attribute->getObject());
                    $valueObject->setObject($subObject);
                }

                // TODO: more validation for type object?
                $subObject = $this->validateEntity($valueObject->getObject(), $value);

                // Push it into our object
                $objectEntity->getValueByAttribute($attribute)->setObject($subObject);
                if (!$this->isAssoc($value)) {
                    $result = [];
                    //TODO: make sure all error messages are returned for each subresource, now only the errors for the last one are returned
                    foreach ($value as $key => $manyToOneSubresource) {
                        $result = array_merge($result, $this->validateEntity($attribute->getObject(), $manyToOneSubresource));
                    }
                    break;
                }
                $result = $this->validateEntity($attribute->getObject(), $value);
                break;
            case 'string':
                if (!is_string($value)) {
                    $objectEntity->addError($attribute->getName(),'Expects ' . $attribute->getType() . ', ' . gettype($value) . ' given.');
                }
                if ($attribute->getMinLength() && strlen($value) < $attribute->getMinLength()) {
                    $objectEntity->addError($attribute->getName(),'Is to short, minimum length is ' . $attribute->getMinLength() . '.');
                }
                if ($attribute->getMaxLength() && strlen($value) > $attribute->getMaxLength()) {
                    $objectEntity->addError($attribute->getName(),'Is to long, maximum length is ' . $attribute->getMaxLength() . '.');
                }
                break;
            case 'number':
                if (!is_integer($value) && !is_float($value) && gettype($value) != 'float' && gettype($value) != 'double') {
                    $objectEntity->addError($attribute->getName(),'Expects ' . $attribute->getType() . ', ' . gettype($value) . ' given.');
                }
                break;
            case 'integer':
                if (!is_integer($value)) {
                    $objectEntity->addError($attribute->getName(),'Expects ' . $attribute->getType() . ', ' . gettype($value) . ' given.');
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
                if (!is_bool($value)) {
                    $objectEntity->addError($attribute->getName(),'Expects ' . $attribute->getType() . ', ' . gettype($value) . ' given.');
                }
                break;
            case 'array':
                if (!is_array($value)) {
                    $objectEntity->addError($attribute->getName(),'Expects ' . $attribute->getType() . ', ' . gettype($value) . ' given.');
                }
                if ($attribute->getMinItems() && count($value) < $attribute->getMinItems()) {
                    $objectEntity->addError($attribute->getName(),'The minimum array length of this attribute is ' . $attribute->getMinItems() . '.');
                }
                if ($attribute->getMaxItems() && count($value) > $attribute->getMaxItems()) {
                    $objectEntity->addError($attribute->getName(),'The maximum array length of this attribute is ' . $attribute->getMaxItems() . '.');
                }
                if ($attribute->getUniqueItems() && count(array_filter(array_keys($value), 'is_string')) == 0) {
                    // TODO:check this in another way so all kinds of arrays work with it.
                    $containsStringKey = false;
                    foreach ($value as $arrayItem) {
                        if (is_array($arrayItem) && count(array_filter(array_keys($arrayItem), 'is_string')) > 0){
                            $containsStringKey = true; break;
                        }
                    }
                    if (!$containsStringKey && count($value) !== count(array_unique($value))) {
                        $objectEntity->addError($attribute->getName(),'Must be an array of unique items');
                    }
                }
                break;
            case 'datetime':
                try {
                    new \DateTime($value);
                } catch (HttpException $e) {
                    $objectEntity->addError($attribute->getName(),'Expects ' . $attribute->getType() . ', failed to parse string to DateTime.');
                }
                break;
            default:
                $objectEntity->addError($attribute->getName(),'has an an unknown type: [' . $attributeType . ']');
        }

        // if not we can set the value
        if(!$objectEntity->hasErrors()){
            $objectEntity->getValueByAttribute($attribute)->setValue($value);
        }

        return $objectEntity;
    }


    function createPromise (ObjectEntity $objectEntity, array $post){

        // Async aanroepen van de promise methode in cg bundel
        $promise = $client->requestAsync('GET', 'http://httpbin.org/get', $post);

        // Creating a promise
        $promise->then(
            function (ResponseInterface $response, ObjectEntity $objectEntity) {
                $object = json_decode($response->getBody()->getContents(), true);
                $objectEntity->setUri($object['@id']);
            },
            function (RequestException $e, ObjectEntity $objectEntity) {
                $objectEntity->addError($objectEntity->name,$e->getMessage());
            }
        );

        return $promise;
    }
}
