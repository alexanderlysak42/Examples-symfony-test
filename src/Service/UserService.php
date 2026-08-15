<?php

namespace App\Service;

use App\Entity\User;
use App\Exception\DuplicateUserException;
use App\Repository\UsersRepository;
use App\Requests\CreateUserRequest;
use App\Requests\UpdateUserRequest;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserService
{
    public function __construct(
        private readonly UsersRepository $users,
        private readonly EntityManagerInterface $em,
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {
    }

    public function findAll(): array
    {
        return $this->users->findAll();
    }

    public function create(CreateUserRequest $request): User
    {
        if ($this->users->userExists($request->login, $request->pass)) {
            throw new DuplicateUserException();
        }

        $user = new User();
        $user->setLogin($request->login);
        $user->setPhone($request->phone);
        $user->setPass($this->passwordHasher->hashPassword($user, $request->pass));

        $this->em->persist($user);
        $this->em->flush();

        return $user;
    }

    public function update(User $user, UpdateUserRequest $request): User
    {
        $user->setLogin($request->login);
        $user->setPhone($request->phone);

        if ($request->pass !== null && $request->pass !== '') {
            if ($this->users->userExists($request->login, $request->pass, $user->getId())) {
                throw new DuplicateUserException();
            }

            $user->setPass($this->passwordHasher->hashPassword($user, $request->pass));
        }

        $this->em->flush();

        return $user;
    }

    public function delete(User $user): void
    {
        $this->em->remove($user);
        $this->em->flush();
    }
}
