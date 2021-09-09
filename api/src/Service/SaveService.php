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
    private EntityManagerInterface $em;
    private CommonGroundService $commonGroundService;
    private ParameterBagInterface $params;
    private $saveStack;

    public function __construct(EntityManagerInterface $em, CommonGroundService $commonGroundService, ParameterBagInterface $params)
    {
        $this->em = $em;
        $this->commonGroundService = $commonGroundService;
        $this->params = $params;
    }

    /*@todo docs */
    function saveEntity (Entity $entity, array $postValues){

        // Does the entity already exist?
        if (isset($postValues['@self'])) {
            // Get existing object with @self
            $object = $this->em->getRepository("App\Entity\ObjectEntity")->findOneBy(['uri' => $postValues['@self']]);
            if (empty($object)) {
                //TODO: change error handling
                throw new HttpException('No object found with this @self: ' . $postValues['@self'] . ' !', 400);
            }
        } elseif (isset($postValues['id'])) {
            // Get existing object with id
            $object = $this->em->getRepository("App\Entity\ObjectEntity")->findOneBy(['id' => $postValues['id']]);
            if (empty($object)) {
                //TODO: change error handling
                throw new HttpException('No eav/ObjectEntity object found with this uuid: ' . $postValues['id'] . ' !', 400);
            }
        }
        // If not create it
        else{
            $object = New ObjectEntity();
            $object->setEntity($entity);
            $object->setUri($this->createUri($entity->getType(), $object->getId())); //TODO: used for setting @eav... getId does not seem to work like this
        }

        $object = $this->prepareEntity($entity,  $object, $postValues);

        // Save the object
        $this->em->persist($object);
        // Last but nog least we flush the doctrine commands
        $this->em->flush();

        // Return the object for rendering
        return $object;
    }

    /*@todo docs */
    /**
     * @throws \Exception
     */
    function prepareEntity (Entity $entity, ObjectEntity $object, array $postValues){

        // So now we have a matching object for our entity
        foreach($entity->getAttributes() as $attribute){

            $value = $object->getValueByAttribute($attribute);
            $value->setUri('https://example.com'); //TODO: remove after making it not a required field?

            // Check for nested objects
            if($attribute->getType() == 'object') {
                // check if subobject already exists and if not create a new ObjectEntity
                $subObject = $this->saveEntity($attribute->getObject(), $postValues[$attribute->getName()]);
                $value->setValue($subObject);
                $this->em->persist($value);
                continue;
            }
            else{
                $value->setValue($postValues[$attribute->getName()]);
                $this->em->persist($value);
                continue;
            }

            // something whent wrong, we are not supposed to end up here
            //throw Exception::

        }

        return $object;
    }

    //TODO: make this better? and use it to generate the @id for eav objects.
    private function createUri($type, $id)
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

    public function renderResult(ObjectEntity $result): array
    {
        $response = [];

        $response['@context'] = '/contexts/' . ucfirst($result->getEntity()->getName());
        $response['@id'] = $result->getUri();
        $response['@type'] = ucfirst($result->getEntity()->getName());
        $response['id'] = $result->getId();
        $response['@self'] = $response['@id'];
        $response['@eav'] = $response['@id'];
        $response['@eavType'] = $response['@type'];
        $response['eavId'] = $response['id'];

        foreach ($result->getObjectValues() as $value) {
            $attribute = $value->getAttribute();
            if ($attribute->getType() == 'object') {
                $response[$attribute->getName()] = $this->renderResult($value->getValue());
                continue;
            }
            $response[$attribute->getName()] = $value->getValue();
        }

        return $response;
    }
}
