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
    function validateEntity (Entity $entity, array $post){

        $results = [];

        foreach($entity->getAttributes() as $attribute){

            // check if we have a value to validate
            if(key_exisist($attribute->getName(), $post)){
                $result = $this->validateAtribute($attribute, $post[$attribute->getName]);
                // is we actuely get a result we need to stick it to the result array
                if($result && !empty($result)){
                    $results[getName()] = $result;
                }
            }

            // its not there but should it be?
            elseif($attribute->getRequired()){
                $results[$attribute->getName()] = 'this attribute is required';
                continue;
            }

            // its not so no problemo
            continue;

            /* @todo handling the setting to null of exisiting variables */
        }
        return $results ;
    }

    /*@todo docs */
    function validateAttribute (Attribute $attribute, $value){

        // lets catch nested entity's
        if($attribute->getType() == 'object'){
            $result = $this->validateEntity($attribute->getObject(), $value);
        }

        // de normale validatie switches
        .....

        return $result;
    }


}
