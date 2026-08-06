<?php

namespace App\Entity;

use App\Repository\UsersRepository;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: UsersRepository::class)]
#[ORM\Table(name: 'user')]
#[ORM\UniqueConstraint(name: 'uniq_login_pass', columns: ['login', 'pass'])]
class User implements PasswordAuthenticatedUserInterface, UserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['user:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 8)]
    #[Groups(['user:read'])]
    private string $login;

    #[ORM\Column(length: 13)]
    #[Groups(['user:read'])]
    private string $phone;

    #[ORM\Column(length: 255)]
    private string $pass;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLogin(): string
    {
        return $this->login;
    }

    public function setLogin(string $login): static
    {
        $this->login = $login;
        return $this;
    }

    public function getPhone(): string
    {
        return $this->phone;
    }

    public function setPhone(string $phone): static
    {
        $this->phone = $phone;
        return $this;
    }

    public function setPass(string $pass): static
    {
        $this->pass = $pass;
        return $this;
    }

    public function getPassword(): string
    {
        return $this->pass;
    }

    public function getUserIdentifier(): string
    {
        return $this->login;
    }

    public function getRoles(): array
    {
        return ['ROLE_USER'];
    }

    public function eraseCredentials(): void
    {}
}
