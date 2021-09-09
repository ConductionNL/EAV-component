<?php

namespace App\Service;

use App\Entity\Attribute;
use App\Entity\Entity;
use App\Entity\ObjectEntity;
use App\Entity\Value;
use Conduction\CommonGroundBundle\Service\CommonGroundService;
use Doctrine\ORM\EntityManagerInterface;
use http\Exception;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use SensioLabs\Security\Exception\HttpException;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Paginator;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\String\Inflector\EnglishInflector;

class SaveService
{
    private $em;
    private $saveStack;

    /*@todo docs */
    function saveEntity (Entity $entity, array $postValues){

        // Does the entity already exist?
        if($postValues['id']){
            $object = $em->getentitn bla bla
        }
        // If not create it
        else{
            $object = New ObjectEntity();
        }

        $object = $this->prepareEntity( $entity,  $object, $postvalues);

        // Save the object
        $this->em->persist($object);
        // Last but nog least we flush the doctrine commands
        $this->em->flush();

        // Return the object for rendering
        return $object;
    }

    /*@todo docs */
    function prepareEntity (Entity $entity, ObjectEntity $object, array $postValues){

        // So now we have a matching object for our entity
        foreach($entity->getAttributes() as $attribute){

            $value = $object->getValue($attribute);

            // Check for nested objects
            if($attribute->getType() == 'object'){
                $subObject = $this->prepareEntity( $entity,  $object, $postValues[$attribute->getName()]);
                $value->setObject($subObject);
                $this->em->persist();
                continue;
            }
            else{
                // zie https://conduction.atlassian.net/browse/BISC-393
                $value->setValue($postValues[$attribute->getName()]);
                $this->em->persist($value);
                continue;
            }

            // something whent wrong, we are not supposed to end up here
            //throw Exception::

        }

        return $object;
    }

}
