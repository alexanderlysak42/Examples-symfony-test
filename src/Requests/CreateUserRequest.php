<?php

namespace App\Requests;

use Symfony\Component\Validator\Constraints as Assert;

class CreateUserRequest
{
    #[Assert\NotBlank]
    #[Assert\Length(max: 8)]
    public string $login = '';

    #[Assert\NotBlank]
    #[Assert\Length(max: 13)]
    public string $phone = '';

    #[Assert\NotBlank]
    #[Assert\Length(max: 8)]
    public string $pass = '';

    #[Assert\NotBlank]
    #[Assert\IdenticalTo(propertyPath: 'pass', message: 'Password confirmation does not match.')]
    public string $passConfirmation = '';
}
