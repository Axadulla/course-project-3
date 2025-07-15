<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`app_user`')]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
#[UniqueEntity(fields: ['email'], message: 'There is already an account with this email')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    private ?string $email = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    /**
     * @var Collection<int, FormTemplate>
     */
    #[ORM\OneToMany(
        targetEntity: FormTemplate::class,
        mappedBy: 'owner',
        cascade: ['remove'],
        orphanRemoval: true
    )]
    private Collection $formTemplates;

    /**
     * @var Collection<int, Like>
     */
    #[ORM\OneToMany(targetEntity: Like::class, mappedBy: 'author', orphanRemoval: true)]
    private Collection $likes;

    /**
     * @var Collection<int, FormSubmission>
     */
    #[ORM\OneToMany(targetEntity: FormSubmission::class, mappedBy: 'owner')]
    private Collection $formSubmissions;


    public function __construct()
    {
        $this->formTemplates = new ArrayCollection();
        $this->likes = new ArrayCollection();
        $this->formSubmissions = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {

    }

    /**
     * @return Collection<int, FormTemplate>
     */
    public function getFormTemplates(): Collection
    {
        return $this->formTemplates;
    }

    public function addFormTemplate(FormTemplate $formTemplate): static
    {
        if (!$this->formTemplates->contains($formTemplate)) {
            $this->formTemplates->add($formTemplate);
            $formTemplate->setOwner($this);
        }

        return $this;
    }

    public function removeFormTemplate(FormTemplate $formTemplate): static
    {
        if ($this->formTemplates->removeElement($formTemplate)) {
            if ($formTemplate->getOwner() === $this) {
                $formTemplate->setOwner(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Like>
     */
    public function getLikes(): Collection
    {
        return $this->likes;
    }

    public function addLike(Like $like): static
    {
        if (!$this->likes->contains($like)) {
            $this->likes->add($like);
            $like->setAuthor($this);
        }

        return $this;
    }

    public function removeLike(Like $like): static
    {
        if ($this->likes->removeElement($like)) {
            if ($like->getAuthor() === $this) {
                $like->setAuthor(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, FormSubmission>
     */
    public function getFormSubmissions(): Collection
    {
        return $this->formSubmissions;
    }

    public function addFormSubmission(FormSubmission $formSubmission): static
    {
        if (!$this->formSubmissions->contains($formSubmission)) {
            $this->formSubmissions->add($formSubmission);
            $formSubmission->setOwner($this);
        }

        return $this;
    }

    public function removeFormSubmission(FormSubmission $formSubmission): static
    {
        if ($this->formSubmissions->removeElement($formSubmission)) {
            // set the owning side to null (unless already changed)
            if ($formSubmission->getOwner() === $this) {
                $formSubmission->setOwner(null);
            }
        }

        return $this;
    }
}
