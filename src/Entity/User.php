<?php declare(strict_types=1);

namespace App\Entity;

use DateTime;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Exception;

/**
 * @ORM\Entity()
 * @ORM\HasLifecycleCallbacks
 */
class User
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="UUID")
     * @ORM\Column(type="guid")
     */
    private ?string $id = null;

    /**
     * @Assert\Length(min=3, max=255, normalizer="trim")
     * @Assert\NotBlank
     * @ORM\Column(type="string", length=255)
     */
    private ?string $name = null;

    /**
     * @Assert\Type("DateTime")
     * @ORM\Column(type="date", nullable=true)
     */
    private ?DateTimeInterface $birthday = null;

    /**
     * @var Phone[]|Collection
     * @ORM\OneToMany(targetEntity=Phone::class, mappedBy="user", orphanRemoval=true, cascade={"persist", "remove"})
     */
    private Collection $phones;

    /**
     * @ORM\Column(name="created_at", type="datetime")
     */
    private ?DateTime $createdAt = null;

    /**
     * @ORM\Column(name="updated_at", type="datetime", nullable=true)
     */
    private ?DateTime $updatedAt = null;

    public function __construct()
    {
        $this->phones = new ArrayCollection();
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getBirthday(): ?DateTimeInterface
    {
        return $this->birthday;
    }

    public function setBirthday(?DateTimeInterface $birthday): self
    {
        $this->birthday = $birthday;

        return $this;
    }

    /**
     * @return Collection|Phone[]
     */
    public function getPhones(): Collection
    {
        return $this->phones;
    }

    public function addPhone(Phone $phone): self
    {
        if (!$this->phones->contains($phone)) {
            $this->phones[] = $phone;
            $phone->setUser($this);
        }

        return $this;
    }

    public function removePhone(Phone $phone): self
    {
        if ($this->phones->removeElement($phone)) {
            // set the owning side to null (unless already changed)
            if ($phone->getUser() === $this) {
                $phone->setUser(null);
            }
        }

        return $this;
    }

    public function getCreatedAt(): ?DateTime
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?DateTime
    {
        return $this->updatedAt;
    }

    /**
     * @ORM\PrePersist()
     * @throws Exception
     */
    public function createDate(): void
    {
        $this->createdAt = new DateTime();
    }

    /**
     * @ORM\PreUpdate()
     * @throws Exception
     */
    public function updateDate(): void
    {
        $this->updatedAt = new DateTime();
    }

    public function getPhoneNumbers(): ?ArrayCollection
    {
        $phones = new ArrayCollection();
        foreach($this->getPhones() as $phone) {
            $phones->add($phone->get());
        }

        return $phones->count() ? $phones : null;
    }
}
