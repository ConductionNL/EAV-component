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

class CallService
{
    private $commonGroundService;
    private $callStack;

    /*@todo docs */
    function postEntity (Entity $entity, array $post){

        // Check for nested objects
        foreach($entity->getAttributes() as $attribute){
            if($attribute->getType() == 'object'){
                $this->validateEntity($attribute->getObject(), $post[$attribute->getName()]);
            }
        }

        // Add a call to the call stack
        $this->callStack[] = createCall();

        return $this->callStack ;
    }

    function createCall (Entity $entity, array $post){

    }



}
