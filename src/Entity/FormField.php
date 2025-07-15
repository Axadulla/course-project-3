<?php

namespace App\Entity;

use App\Repository\FormFieldRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FormFieldRepository::class)]
class FormField
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'fields')]
    #[ORM\JoinColumn(nullable: false)]
    private ?FormTemplate $formTemplate = null;

    #[ORM\Column(length: 255)]
    private ?string $label = null;

    #[ORM\Column(length: 50)]
    private ?string $type = null;

    #[ORM\Column(nullable: true)]
    private ?array $options = null;

    #[ORM\Column]
    private ?bool $required = null;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFormTemplate(): ?FormTemplate
    {
        return $this->formTemplate;
    }

    public function setFormTemplate(?FormTemplate $formTemplate): static
    {
        $this->formTemplate = $formTemplate;

        return $this;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(string $label): static
    {
        $this->label = $label;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getOptions(): ?array
    {
        return $this->options;
    }

    public function setOptions(?array $options): static
    {
        $this->options = $options;

        return $this;
    }

    public function isRequired(): ?bool
    {
        return $this->required;
    }

    public function setRequired(bool $required): static
    {
        $this->required = $required;

        return $this;
    }


    #[ORM\Column(name: '`order`', type: 'integer')]
    private int $order = 0;

    public function getOrder(): ?int
    {
        return $this->order;
    }

    public function setOrder(int $order): self
    {
        $this->order = $order;
        return $this;
    }



    #[\Doctrine\ORM\Mapping\Transient]
    private ?string $optionsRaw = null;

    /**
     * @var Collection<int, FormAnswer>
     */
    #[ORM\OneToMany(targetEntity: FormAnswer::class, mappedBy: 'field')]
    private Collection $answers;

    public function __construct()
    {
        $this->answers = new ArrayCollection();
    }


    public function getOptionsRaw(): ?string
    {
        return $this->optionsRaw;
    }

    public function setOptionsRaw(?string $optionsRaw): void
    {
        $this->optionsRaw = $optionsRaw;
    }

    /**
     * @return Collection<int, FormAnswer>
     */
    public function getAnswers(): Collection
    {
        return $this->answers;
    }

    public function addAnswer(FormAnswer $answer): static
    {
        if (!$this->answers->contains($answer)) {
            $this->answers->add($answer);
            $answer->setField($this);
        }

        return $this;
    }

    public function removeAnswer(FormAnswer $answer): static
    {
        if ($this->answers->removeElement($answer)) {
            // set the owning side to null (unless already changed)
            if ($answer->getField() === $this) {
                $answer->setField(null);
            }
        }

        return $this;
    }


}
