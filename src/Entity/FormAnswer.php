<?php

namespace App\Entity;

use App\Repository\FormAnswerRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FormAnswerRepository::class)]
class FormAnswer
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'answers')]
    #[ORM\JoinColumn(nullable: false)]
    private ?FormSubmission $submission = null;

    #[ORM\ManyToOne(inversedBy: 'answers')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?FormField $field = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $value = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSubmission(): ?FormSubmission
    {
        return $this->submission;
    }

    public function setSubmission(?FormSubmission $submission): static
    {
        $this->submission = $submission;

        return $this;
    }

    public function getField(): ?FormField
    {
        return $this->field;
    }

    public function setField(?FormField $field): static
    {
        $this->field = $field;

        return $this;
    }

    public function getValue(): ?string
    {
        return $this->value;
    }

    public function setValue(?string $value): static
    {
        $this->value = $value;

        return $this;
    }
}
