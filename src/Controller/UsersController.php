<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UsersRepository;
use App\Requests\CreateUserRequest;
use App\Requests\UpdateUserRequest;
use App\Security\Access\UserAccess;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/v1/api/users')]
class UsersController extends AbstractController
{
    public function __construct(
        private readonly UsersRepository $users,
        private readonly EntityManagerInterface $em,
        private readonly UserPasswordHasherInterface $passwordHasher,
    ){}

    #[Route('', methods: ['GET'])]
    public function usersList(): JsonResponse
    {
        if ($this->isGranted('ROLE_ROOT')) {
            $users = $this->users->findAll();
        } else {
            $userItem = $this->getUser();
            $users = $userItem instanceof User ? [$userItem] : [];
        }

        return $this->json(
            $users,
            Response::HTTP_OK,
            [],
            ['groups' => 'user:read']
        );
    }

    #[Route('/{id}', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function usersShow(User $user): JsonResponse
    {
        $this->denyAccessUnlessGranted(UserAccess::VIEW, $user);

        return $this->json(
            $user,
            Response::HTTP_OK,
            [],
            ['groups' => 'user:read']
        );
    }

    #[Route('', methods: ['POST'])]
    public function createUser(#[MapRequestPayload] CreateUserRequest $createUserRequest): JsonResponse
    {
        $this->denyAccessUnlessGranted(UserAccess::CREATE);

        if ($this->users->userExists($createUserRequest->login, $createUserRequest->pass)) {
            return $this->json(['error' => 'duplicate_entry'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $user = new User();
        $user->setLogin($createUserRequest->login);
        $user->setPhone($createUserRequest->phone);
        $user->setPass($this->passwordHasher->hashPassword($user, $createUserRequest->pass));

        $this->em->persist($user);
        $this->em->flush();

        return $this->json(
            $user,
            Response::HTTP_CREATED,
            [],
            ['groups' => 'user:read']
        );
    }

    #[Route('/{id}', requirements: ['id' => '\d+'], methods: ['PUT'])]
    public function updateUser(User $user, #[MapRequestPayload] UpdateUserRequest $updateUserRequest): JsonResponse
    {
        $this->denyAccessUnlessGranted(UserAccess::EDIT, $user);

        $user->setLogin($updateUserRequest->login);
        $user->setPhone($updateUserRequest->phone);

        if ($updateUserRequest->pass !== null && $updateUserRequest->pass !== '') {
            if ($this->users->userExists($updateUserRequest->login, $updateUserRequest->pass, $user->getId())) {
                return $this->json(['error' => 'duplicate_entry'], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $user->setPass($this->passwordHasher->hashPassword($user, $updateUserRequest->pass));
        }

        $this->em->flush();

        return $this->json(
            $user,
            Response::HTTP_OK,
            [],
            ['groups' => 'user:read']
        );
    }

    #[Route('/{id}', requirements: ['id' => '\d+'], methods: ['DELETE'])]
    public function deleteUser(User $user): JsonResponse
    {
        $this->denyAccessUnlessGranted(UserAccess::DELETE, $user);

        $this->em->remove($user);
        $this->em->flush();

        return new JsonResponse(
            null,
            Response::HTTP_NO_CONTENT
        );
    }

}
