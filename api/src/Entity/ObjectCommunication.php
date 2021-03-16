<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ApiResource()
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
     * @var string The type of action we are doing
     *
     * @example get
     *
     * @Assert\Choice({"get", "post", "put"})
     *
     * @Groups({"read"})
     * @ORM\Column(type="string", length=255)
     */
    private $action = 'get';

    /**
     * @var string (get, post and put) The component for the objectEntity we are getting, creating or updating
     *
     * @Groups({"read"})
     * @ORM\Column(type="string", length=255)
     */
    private $component = 'eav';

    /**
     * @var string (get, post and put) The entity type for the objectEntity we are getting, creating or updating
     *
     * @Groups({"read"})
     * @ORM\Column(type="string", length=255)
     */
    private $entityType;

    /**
     * @var string (get or put) The uuid of the objectEntity we are getting or updating
     *
     * @Groups({"read"})
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $objectEntityId;

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getAction(): ?string
    {
        return $this->action;
    }

    public function getComponent(): ?string
    {
        return $this->component;
    }

    public function getType(): ?string
    {
        return $this->entityType;
    }

    public function getObjectEntityId(): ?string
    {
        return $this->objectEntityId;
    }
}
