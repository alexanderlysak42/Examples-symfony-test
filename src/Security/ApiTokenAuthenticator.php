<?php

namespace App\Security;

use App\Repository\UsersRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\InMemoryUser;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

class ApiTokenAuthenticator extends AbstractAuthenticator
{
    public function __construct(
        private readonly UsersRepository $users,
        private readonly string $rootApiToken,
    ) {}

    public function supports(Request $request): ?bool
    {
        return str_starts_with((string) $request->headers->get('Authorization'), 'Bearer ');
    }

    public function authenticate(Request $request): Passport
    {
        $token = substr((string) $request->headers->get('Authorization'), strlen('Bearer '));

        if ($this->rootApiToken !== '' && hash_equals($this->rootApiToken, $token)) {
            return new SelfValidatingPassport(
                new UserBadge('root', fn () => new InMemoryUser('root', null, ['ROLE_ROOT']))
            );
        }

        $decoded = base64_decode($token, true);
        if ($decoded === false || !str_contains($decoded, ':')) {
            throw new AuthenticationException('Invalid API token');
        }

        [$login, $pass] = explode(':', $decoded, 2);

        return new SelfValidatingPassport(
            new UserBadge($login, function () use ($login, $pass): object {
                $user = $this->users->findOneByLoginAndPassword(['login' => $login, 'pass' => $pass]);
                if ($user === null) {
                    throw new AuthenticationException('Invalid credentials');
                }

                return $user;
            })
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        return null;
    }
}
