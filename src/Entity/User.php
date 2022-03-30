<?php


namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Persistence\Mapping\ClassMetadata;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Persistence\ObjectManagerAware;
use Gedmo\Mapping\Annotation as Gedmo;
use JMS\Serializer\Annotation as Serializer;
use Serializable;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @ORM\Table(name="users", indexes={
 *     @ORM\Index(columns={"name"}),
 *     })
 * @UniqueEntity(fields="email", message="this e-mail already used")
 * @Gedmo\Loggable
 */
class User implements UserInterface, Serializable, ObjectManagerAware
{
    private const SALT_ENTROPY = 8;//bytes

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     *
     * @Serializer\Groups({"api"})
     *
     * @var int
     */
    private $id;
    /**
     * @ORM\Column(type="string", length=100, unique=true)
     *
     * @Serializer\Groups({"api"})
     *
     * @Assert\Email
     *
     * @Gedmo\Versioned
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=64)
     *
     * @Assert\NotBlank
     *
     * @Gedmo\Versioned
     *
     * @var string
     */
    private $salt;

    /**
     * @ORM\Column(type="string", length=64)
     *
     * @Assert\NotBlank
     *
     * @Gedmo\Versioned
     *
     * @var string
     */
    private $password;

    /**
     * @ORM\Column(type="string", length=192, nullable=false)
     *
     * @Serializer\Groups({"api"})
     *
     * @Gedmo\Versioned
     *
     * @var string
     */
    private string $name = '';

    private ObjectManager $em;

    /**
     * @ORM\ManyToMany(targetEntity=Group::class, inversedBy="users")
     */
    private $groups;

    /**
     * User constructor.
     */
    public function __construct()
    {
        $this->salt = base64_encode(random_bytes(self::SALT_ENTROPY));
        $this->groups = new ArrayCollection();
    }

    /**
     * @return string
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * @param string|null $password
     *
     * @return $this
     */
    public function setPassword(?string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * @param string $email
     *
     * @return $this
     */
    public function setEmail(?string $email): self
    {
        $this->email = $email ? strtolower($email) : null;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getRoles()
    {
        return [];
    }

    /**
     * {@inheritDoc}
     */
    public function getUsername()
    {
        return $this->email;
    }

    /**
     * {@inheritDoc}
     *
     * @return void
     */
    public function eraseCredentials()
    {
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }


    /**
     * {@inheritDoc}
     */
    public function serialize()
    {
        return serialize(
            $this->__serialize()
        );
    }

    public function __serialize(): array
    {
        return [
            $this->id,
            $this->email,
            $this->salt,
            $this->password,
            $this->name
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function unserialize($serialized)
    {
        list(
            $this->id,
            $this->email,
            $this->salt,
            $this->password,
            $this->name
            ) = unserialize($serialized, ['allowed_classes' => true]);
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     *
     * @return $this
     */
    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @param ObjectManager $objectManager
     * @param ClassMetadata $classMetadata
     */
    public function injectObjectManager(ObjectManager $objectManager, ClassMetadata $classMetadata)
    {
        $this->em = $objectManager;
    }

    public function getSalt()
    {
        return $this->salt;
    }

    public function __unserialize(array $data): void
    {
        $this->id = $data[0];
        $this->email = $data[1];
        $this->password = $data[2];
        $this->name = $data[3];
    }

    public function getUserIdentifier(): int
    {
        return $this->id;
    }

    public function getGroups()
    {
        return $this->groups;
    }
    public function addGroup(Group $tag): self
    {
        if (!$this->groups->contains($tag)) {
            $this->groups[] = $tag;
        }
        return $this;
    }
    public function removeGroup(Group $tag): self
    {
        $this->groups->removeElement($tag);
        return $this;
    }
}
