<?php

namespace App\Controller;

use App\Dto\Response\UserResponse;
use App\Entity\User;
use App\Requests\CreateUserRequest;
use App\Requests\UpdateUserRequest;
use App\Security\Access\UserAccess;
use App\Service\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/v1/api/users')]
class UsersController extends AbstractController
{
    public function __construct(
        private readonly UserService $userService,
    ) {}

    #[Route('', methods: ['GET'])]
    public function usersList(): JsonResponse
    {
        if ($this->isGranted('ROLE_ROOT')) {
            $users = $this->userService->findAll();
        } else {
            $userItem = $this->getUser();
            $users = $userItem instanceof User ? [$userItem] : [];
        }

        return $this->json(UserResponse::fromEntities($users), Response::HTTP_OK);
    }

    #[Route('/{id}', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function usersShow(User $user): JsonResponse
    {
        $this->denyAccessUnlessGranted(UserAccess::VIEW, $user);

        return $this->json(UserResponse::fromEntity($user), Response::HTTP_OK);
    }

    #[Route('', methods: ['POST'])]
    public function createUser(#[MapRequestPayload] CreateUserRequest $createUserRequest): JsonResponse
    {
        $this->denyAccessUnlessGranted(UserAccess::CREATE);

        $user = $this->userService->create($createUserRequest);

        return $this->json(UserResponse::fromEntity($user), Response::HTTP_CREATED);
    }

    #[Route('/{id}', requirements: ['id' => '\d+'], methods: ['PUT'])]
    public function updateUser(User $user, #[MapRequestPayload] UpdateUserRequest $updateUserRequest): JsonResponse
    {
        $this->denyAccessUnlessGranted(UserAccess::EDIT, $user);

        $user = $this->userService->update($user, $updateUserRequest);

        return $this->json(UserResponse::fromEntity($user), Response::HTTP_OK);
    }

    #[Route('/{id}', requirements: ['id' => '\d+'], methods: ['DELETE'])]
    public function deleteUser(User $user): JsonResponse
    {
        $this->denyAccessUnlessGranted(UserAccess::DELETE, $user);

        $this->userService->delete($user);

        return new JsonResponse(
            null,
            Response::HTTP_NO_CONTENT
        );
    }

}
