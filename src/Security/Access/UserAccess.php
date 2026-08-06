<?php

namespace App\Security\Access;

use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class UserAccess extends Voter
{
    public const VIEW = 'USER_VIEW';
    public const EDIT = 'USER_EDIT';
    public const DELETE = 'USER_DELETE';
    public const CREATE = 'USER_CREATE';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::VIEW, self::EDIT, self::DELETE, self::CREATE], true)
            && ($subject instanceof User || $subject === null);
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        if (in_array('ROLE_ROOT', $token->getRoleNames(), true)) {
            return true;
        }

        $user = $token->getUser();

        return match ($attribute) {
            self::VIEW,
            self::EDIT => $subject instanceof User && $user instanceof User && $subject->getId() === $user->getId(),
            self::DELETE,
            self::CREATE => false,
            default => false
        };
    }
}
