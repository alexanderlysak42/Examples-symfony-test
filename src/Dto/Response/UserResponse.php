<?php

namespace App\Dto\Response;

use App\Entity\User;

final class UserResponse
{
    public function __construct(
        public readonly int $id,
        public readonly string $login,
        public readonly string $phone,
    ) {
    }

    public static function fromEntity(User $user): self
    {
        return new self($user->getId(), $user->getLogin(), $user->getPhone());
    }

    public static function fromEntities(array $users): array
    {
        return array_map(self::fromEntity(...), $users);
    }
}
