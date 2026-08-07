<?php

namespace App\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class UsersControllerTest extends WebTestCase
{
    public function testGetUsersWithoutTokenReturns401(): void
    {
        $client = self::createClient();

        $client->request('GET', '/v1/api/users');

        self::assertResponseStatusCodeSame(401);

        self::assertJsonStringEqualsJsonString(
            '{"error":"unauthorized"}',
            $client->getResponse()->getContent()
        );

    }

}
