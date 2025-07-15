<?php

namespace App\Entity;

use App\Repository\FormSubmissionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\User;


#[ORM\Entity(repositoryClass: FormSubmissionRepository::class)]
class FormSubmission
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'formSubmissions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    /**
     * @var Collection<int, FormAnswer>
     */
    #[ORM\OneToMany(targetEntity: FormAnswer::class, mappedBy: 'submission', orphanRemoval: true)]
    private Collection $answers;


    public function __construct()
    {
        $this->answers = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
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
            $answer->setSubmission($this);
        }

        return $this;
    }

    public function removeAnswer(FormAnswer $answer): static
    {
        if ($this->answers->removeElement($answer)) {
            // set the owning side to null (unless already changed)
            if ($answer->getSubmission() === $this) {
                $answer->setSubmission(null);
            }
        }

        return $this;
    }

    #[ORM\ManyToOne(targetEntity: FormTemplate::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?FormTemplate $template = null;


    public function getTemplate(): ?FormTemplate
    {
        return $this->template;
    }

    public function setTemplate(?FormTemplate $template): self
    {
        $this->template = $template;

        return $this;
    }


    #[ORM\Column(type: 'datetime_immutable')]
    private ?\DateTimeImmutable $submittedAt = null;

    public function getSubmittedAt(): ?\DateTimeImmutable
    {
        return $this->submittedAt;
    }

    public function setSubmittedAt(\DateTimeImmutable $submittedAt): self
    {
        $this->submittedAt = $submittedAt;
        return $this;
    }


}
