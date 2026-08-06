<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UsersRepository extends ServiceEntityRepository
{
    public function __construct(
        ManagerRegistry $registry,
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {
        parent::__construct($registry, User::class);
    }

    public function findOneByLoginAndPassword(array $credentials): ?User
    {
        $login = $credentials['login'] ?? '';
        $pass = $credentials['pass'] ?? '';

        $users = $this->findBy(['login' => $login]);
        foreach ($users as $user) {
            if ($this->passwordHasher->isPasswordValid($user, $pass)) {
                return $user;
            }
        }

        return null;
    }

    public function userExists(string $login, string $pass, ?int $excludeId = null): bool
    {
        $users = $this->findBy(['login' => $login]);
        foreach ($users as $user) {
            if ($excludeId !== null && $user->getId() === $excludeId) {
                continue;
            }
            if ($this->passwordHasher->isPasswordValid($user, $pass)) {
                return true;
            }
        }

        return false;
    }
}
