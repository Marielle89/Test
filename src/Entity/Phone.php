<?php declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Datetime;
use Exception;

/**
 * @ORM\Entity()
 * @ORM\Table(uniqueConstraints={@ORM\UniqueConstraint(name="phone_unique", columns={"operator_id", "number"})})
 */
class Phone
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private ?int $id = null;

    /**
     * @ORM\Column(type="string", length=7)
     */
    private ?string $number = null;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2)
     */
    private ?float $balance = 0.00;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="phones")
     * @ORM\JoinColumn(nullable=false)
     */
    private ?User $user = null;

    /**
     * @ORM\ManyToOne(targetEntity=Country::class, cascade={"persist"})
     */
    private ?Country $country = null;

    /**
     * @ORM\ManyToOne(targetEntity=Operator::class, cascade={"persist"})
     */
    private ?Operator $operator = null;

    /**
     * @ORM\Column(name="created_at", type="datetime")
     */
    private ?DateTime $createdAt = null;

    /**
     * @ORM\Column(name="updated_at", type="datetime", nullable=true)
     */
    private ?DateTime $updatedAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNumber(): ?string
    {
        return $this->number;
    }

    public function setNumber(string $number): self
    {
        $this->number = $number;

        return $this;
    }

    public function getBalance(): ?float
    {
        return $this->balance;
    }

    public function setBalance(?float $balance): self
    {
        $this->balance = $balance;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getCountry(): ?Country
    {
        return $this->country;
    }

    public function setCountry(?Country $country): self
    {
        $this->country = $country;

        return $this;
    }

    public function getOperator(): ?Operator
    {
        return $this->operator;
    }

    public function setOperator(?Operator $operator): self
    {
        $this->operator = $operator;

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

    public function get(): ?string
    {
        if (!$this->getCountry() || !$this->getOperator() || !$this->number) {
            return null;
        }

        return $this->getCountry()->getCode() . $this->getOperator()->getCode() . $this->number;
    }
}
