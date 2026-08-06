<?php

namespace App\Requests;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class UpdateUserRequest
{
    #[Assert\NotBlank]
    #[Assert\Length(max: 8)]
    public string $login = '';

    #[Assert\NotBlank]
    #[Assert\Length(max: 13)]
    public string $phone = '';

    #[Assert\Length(max: 8)]
    public ?string $pass = null;

    public ?string $passConfirmation = null;

    #[Assert\Callback]
    public function validatePassConfirmation(ExecutionContextInterface $context): void
    {
        if ($this->pass !== null && $this->pass !== $this->passConfirmation) {
            $context->buildViolation('Password confirmation does not match.')
                ->atPath('passConfirmation')
                ->addViolation();
        }
    }
}
