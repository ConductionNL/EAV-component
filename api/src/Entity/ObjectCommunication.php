<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Description
 *
 * @category Entity
 *
 * @ApiResource(
 *  collectionOperations={
 *  	"get",
 *  	"post",
 *  })
 * @ORM\Entity(repositoryClass="App\Repository\ObjectCommunicationRepository")
 */
class ObjectCommunication
{
    /**
     * @var UuidInterface UUID of this person
     *
     * @Groups({"read"})
     * @ORM\Id
     * @ORM\Column(type="uuid", unique=true)
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class="Ramsey\Uuid\Doctrine\UuidGenerator")
     */
    private $id;

    /**
     * @var string (get, post and put) The component code for the objectEntity we are getting, creating or updating
     *
     * @Groups({"read"})
     * @ORM\Column(type="string", length=255)
     */
    private $componentCode = 'eav';

    /**
     * @var string (get, post and put) The entity name for the objectEntity we are getting, creating or updating. (this actually needs to be the second value of the entity type, so in case of entity type: cc/people, this would/should be people)
     *
     * @Groups({"read"})
     * @ORM\Column(type="string", length=255)
     */
    private $entityName;

    /**
     * @var string (get or put) The uuid of the objectEntity we are getting or updating
     *
     * @Groups({"read"})
     * @ORM\Column(type="uuid", length=255, nullable=true)
     */
    private $objectEntityId;

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getComponentCode(): ?string
    {
        return $this->componentCode;
    }

    public function setComponentCode(string $componentCode): self
    {
        $this->componentCode = $componentCode;

        return $this;
    }

    public function getEntityName(): ?string
    {
        return $this->entityName;
    }

    public function setEntityName(string $entityName): self
    {
        $this->entityName = $entityName;

        return $this;
    }

    public function getObjectEntityId(): ?string
    {
        return $this->objectEntityId;
    }

    public function setObjectEntityId(string $objectEntityId): self
    {
        $this->objectEntityId = $objectEntityId;

        return $this;
    }
}
