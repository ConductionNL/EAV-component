<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\BooleanFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\DateFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Exception;
use Gedmo\Mapping\Annotation as Gedmo;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Description.
 *
 * @category Entity
 *
 * @ApiResource(
 *     normalizationContext={"groups"={"read"}, "enable_max_depth"=true},
 *     denormalizationContext={"groups"={"write"}, "enable_max_depth"=true},
 *     itemOperations={
 *          "get",
 *          "put",
 *          "delete"
 *     },
 *  collectionOperations={
 *  	"get",
 *  	"post"
 *  })
 * @ORM\Entity(repositoryClass="App\Repository\ValueRepository")
 * @Gedmo\Loggable(logEntryClass="Conduction\CommonGroundBundle\Entity\ChangeLog")
 *
 * @ApiFilter(BooleanFilter::class)
 * @ApiFilter(OrderFilter::class)
 * @ApiFilter(DateFilter::class, strategy=DateFilter::EXCLUDE_NULL)
 * @ApiFilter(SearchFilter::class)
 */
class Value
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

    // TODO:indexeren
    /**
     * @var string An uri
     *
     * @Assert\Url
     * @Groups({"read", "write"})
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $uri;

    // TODO:indexeren
    /**
     * @var string The actual value if is of type string
     *
     * @Groups({"read", "write"})
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $stringValue;

    /**
     * @var integer Integer if the value is type integer
     *
     * @Groups({"read", "write"})
     * @ORM\Column(type="integer", nullable=true)
     */
    private $integerValue;

    /**
     * @var float Float if the value is type number
     *
     * @Groups({"read", "write"})
     * @ORM\Column(type="float", nullable=true)
     */
    private $numberValue;

    /**
     * @var boolean Boolean if the value is type boolean
     *
     * @Groups({"read", "write"})
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $booleanValue;

    /**
     * @var array Array if the value is type array
     *
     * @Groups({"read", "write"})
     * @ORM\Column(type="array", nullable=true)
     */
    private $arrayValue;

    /**
     * @var DateTime DateTime if the value is type DateTime
     *
     * @Groups({"read", "write"})
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $dateTimeValue;

    /**
     * @Groups({"read", "write"})
     * @ORM\OneToMany(targetEntity=ObjectEntity::class, fetch="EAGER", mappedBy="subresourceOf", cascade={"remove"})
     * @ORM\JoinColumn(nullable=true)
     * @MaxDepth(1)
     */
    private ?Collection $objects;

    /**
     * @Groups({"read","write"})
     * @ORM\ManyToOne(targetEntity=Attribute::class, inversedBy="attributeValues")
     * @ORM\JoinColumn(nullable=false)
     * @MaxDepth(1)
     */
    private $attribute;

    /**
     * @Groups({"write"})
     * @ORM\ManyToOne(targetEntity=ObjectEntity::class, inversedBy="objectValues", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(nullable=false)
     * @MaxDepth(1)
     */
    private $objectEntity;

    public function __construct()
    {
        $this->objects = new ArrayCollection();
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function setId(Uuid $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getUri(): ?string
    {
        return $this->uri;
    }

    public function setUri(?string $uri): self
    {
        $this->uri = $uri;

        return $this;
    }

    public function getStringValue(): ?string
    {
        return $this->stringValue;
    }

    public function setStringValue(?string $stringValue): self
    {
        $this->stringValue = $stringValue;

        return $this;
    }

    public function getIntegerValue(): ?int
    {
        return $this->integerValue;
    }

    public function setIntegerValue(?int $integerValue): self
    {
        $this->integerValue = $integerValue;

        return $this;
    }

    public function getNumberValue(): ?float
    {
        return $this->numberValue;
    }

    public function setNumberValue(?float $numberValue): self
    {
        $this->numberValue = $numberValue;

        return $this;
    }

    public function getBooleanValue(): ?bool
    {
        return $this->booleanValue;
    }

    public function setBooleanValue(?bool $booleanValue): self
    {
        $this->booleanValue = $booleanValue;

        return $this;
    }

    public function getArrayValue(): ?array
    {
        return $this->arrayValue;
    }

    public function setArrayValue(?array $arrayValue): self
    {
        $this->arrayValue = $arrayValue;

        return $this;
    }

    public function getDateTimeValue(): ?\DateTimeInterface
    {
        return $this->dateTimeValue;
    }

    public function setDateTimeValue(?\DateTimeInterface $dateTimeValue): self
    {
        $this->dateTimeValue = $dateTimeValue;

        return $this;
    }

    /**
     * @return Collection|Value[]
     */
    public function getObjects(): ?Collection
    {
        return $this->objects;
    }

    public function addObject(ObjectEntity $object): self
    {
        if (!$this->objects->contains($object)) {
            $this->objects[] = $object;
            $object->setSubresourceOf($this);
        }

        return $this;
    }

    public function removeObject(ObjectEntity $object): self
    {
        if ($this->objects->removeElement($object)) {
            // set the owning side to null (unless already changed)
            if ($object->getSubresourceOf() === $this) {
                $object->setSubresourceOf(null);
            }
        }

        return $this;
    }

    public function getAttribute(): ?Attribute
    {
        return $this->attribute;
    }

    public function setAttribute(?Attribute $attribute): self
    {
        $this->attribute = $attribute;

        return $this;
    }

    public function getObjectEntity(): ?ObjectEntity
    {
        return $this->objectEntity;
    }

    public function setObjectEntity(?ObjectEntity $objectEntity): self
    {
        $this->objectEntity = $objectEntity;

        return $this;
    }

    /**
     * @throws Exception
     */
    public function setValue($value)
    {
        if ($this->getAttribute()) {
            if ($this->getAttribute()->getMultiple() && $this->getAttribute()->getType() != 'object') {
                return $this->setArrayValue($value);
                //TODO something about array of datetime's, see how we do it with type object
            }
            switch ($this->getAttribute()->getType()) {
                case 'string':
                    return $this->setStringValue($value);
                case 'integer':
                    return $this->setIntegerValue($value);
                case 'boolean':
                    return $this->setBooleanValue($value);
                case 'number':
                    return $this->setNumberValue($value);
                case 'datetime':
                    return $this->setDateTimeValue(new DateTime($value));
                case 'object':
                    if ($value == null) {
                        return $this;
                    }
                    // if multiple is true value should be an array
                    if ($this->getAttribute()->getMultiple()) {
                        foreach ($value as $object) {
                            $this->addObject($object);
                        }
                        return $this;
                    }
                    // else $value = ObjectEntity::class
                    return $this->addObject($value);
            }
        } else {
            //TODO: correct error handling
            return false;
        }
    }

    public function getValue()
    {
        if ($this->getAttribute()) {
            if ($this->getAttribute()->getMultiple() && $this->getAttribute()->getType() != 'object') {
                return $this->getArrayValue();
                //TODO something about array of datetime's, see how we do it with type object
            }
            switch ($this->getAttribute()->getType()) {
                case 'string':
                    return $this->getStringValue();
                case 'integer':
                    return $this->getIntegerValue();
                case 'boolean':
                    return $this->getBooleanValue();
                case 'number':
                    return $this->getNumberValue();
                case 'datetime':
                    $datetime = $this->getDateTimeValue();
                    return $datetime->format('Y-m-d\TH:i:sP');;
                case 'object':
                    $objects = $this->getObjects();
                    if (!$this->getAttribute()->getMultiple()) {
                        return $objects[0];
                    }
                    if (count($objects) == 0) {
                        return null;
                    }
                    return $objects;
            }
        } else {
            //TODO: correct error handling
            return false;
        }
    }
}
