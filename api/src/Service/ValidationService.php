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
    public function validateEntity (Entity $entity, array $post) {

        $results = [];

        foreach($entity->getAttributes() as $attribute){

            // check if we have a value to validate
            if(key_exists($attribute->getName(), $post)){
                $result = $this->validateAttribute($attribute, $post[$attribute->getName()]);
                // is we actuely get a result we need to stick it to the result array
                if($result && !empty($result)){
                    if (is_array($result)) {
                        // TODO: put $entity->getName() before every string key in this result array
                        $results = array_merge($results, $result);
                    } else {
                        $results[$entity->getName().'.'.$attribute->getName()] = $result;
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
                $results[$attribute->getName()] = 'this attribute is required';
            }

            /* @todo handling the setting to null of exisiting variables */
        }

        return $results;
    }

    /*@todo docs */
    private function validateAttribute(Attribute $attribute, $value) {

        $attributeType = $attribute->getType();

        $result = '';

        // Do validation for attribute depending on its type
        switch ($attributeType) {
            case 'object':
                // TODO: more validation for type object?
                $result = $this->validateEntity($attribute->getObject(), $value);
                break;
            case 'string':
                if (!is_string($value)) {
                    $result = 'Expects ' . $attribute->getType() . ', ' . gettype($value) . ' given.';
                }
                if ($attribute->getMinLength() && strlen($value) < $attribute->getMinLength()) {
                    $result = 'Is to short, minimum length is ' . $attribute->getMinLength() . '.';
                }
                if ($attribute->getMaxLength() && strlen($value) > $attribute->getMaxLength()) {
                    $result = 'Is to long, maximum length is ' . $attribute->getMaxLength() . '.';
                }
                break;
            case 'number':
                if (!is_integer($value) && !is_float($value) && gettype($value) != 'float' && gettype($value) != 'double') {
                    $result = 'Expects ' . $attribute->getType() . ', ' . gettype($value) . ' given.';
                }
                break;
            case 'integer':
                if (!is_integer($value)) {
                    $result = 'Expects ' . $attribute->getType() . ', ' . gettype($value) . ' given.';
                }
                if ($attribute->getMinimum()) {
                    if ($attribute->getExclusiveMinimum() && $value <= $attribute->getMinimum()) {
                        $result = 'Must be higher than ' . $attribute->getMinimum() . '.';
                    } elseif ($value < $attribute->getMinimum()) {
                        $result = 'Must be ' . $attribute->getMinimum() . ' or higher.';
                    }
                }
                if ($attribute->getMaximum()) {
                    if ($attribute->getExclusiveMaximum() && $value >= $attribute->getMaximum()) {
                        $result = 'Must be lower than ' . $attribute->getMaximum() . '.';
                    } elseif ($value > $attribute->getMaximum()) {
                        $result = 'Must be ' . $attribute->getMaximum() . ' or lower.';
                    }
                }
                if ($attribute->getMultipleOf() && $value % $attribute->getMultipleOf() != 0) {
                    $result = 'Must be a multiple of ' . $attribute->getMultipleOf() . ', ' . $value . ' is not a multiple of ' . $attribute->getMultipleOf() . '.';
                }
                break;
            case 'boolean':
                if (!is_bool($value)) {
                    $result = 'Expects ' . $attribute->getType() . ', ' . gettype($value) . ' given.';
                }
                break;
            case 'array':
                if (!is_array($value)) {
                    $result = 'Expects ' . $attribute->getType() . ', ' . gettype($value) . ' given.';
                }
                if ($attribute->getMinItems() && count($value) < $attribute->getMinItems()) {
                    $result = 'Has to few items ( ' . count($value) . ' ), the minimum array length of this attribute is ' . $attribute->getMinItems() . '.';
                }
                if ($attribute->getMaxItems() && count($value) > $attribute->getMaxItems()) {
                    $result = 'Has to many items ( ' . count($value) . ' ), the maximum array length of this attribute is ' . $attribute->getMaxItems() . '.';
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
                        $result = 'Must be an array of unique items!';
                    }
                }
                break;
            case 'datetime':
                try {
                    new \DateTime($value);
                } catch (HttpException $e) {
                    $result = 'Expects ' . $attribute->getType() . ', failed to parse string to DateTime.';
                }
                break;
            default:
                $result = 'The entity type: [' . $attribute->getEntity()->getType() . '] has an attribute: [' . $attribute->getName() . '] with an unknown type: [' . $attributeType . '] !';
        }

        return $result;
    }


}
