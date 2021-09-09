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

class GetService
{
    private $em;
    private $saveStack;

    /*@todo docs */
    function getEntity (Entity $entity, array $post){

        // Does the entity already exist?
        if($post['id']){
//            $object = $em->getentitn bla bla
        }
        else{
            // lets throw an error
        }

        foreach($entity->getAttributes() as $attribute){
            // Check for nested objects
            if($attribute->getType() == 'object'){
                $this->saveEntity($attribute->getObject(), $post[$attribute->getName()]);
            }
            else{
                $this->saveStack[] = createSave($attribute, $post[$attribute->getName()]);
            }
        }

        return $this->saveStack;
    }

    /*@todo docs */
    function getFromRest(Attribute $attribute, $value){
    }
}
