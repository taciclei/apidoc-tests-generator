<?php

declare(strict_types=1);

namespace PhpJit\ApidocTestsGenerator\Traits;

use App\Entity\User;
use Symfony\Contracts\HttpClient\HttpClientInterface;

trait ClientTrait
{
    public static function getClient(?string $token = null): HttpClientInterface
    {
        return self::createClient([], [
            'headers' => [
                'Accept' => 'application/ld+json',
                'Content-Type' => 'application/ld+json',
                'Authorization' => $token ? sprintf('Bearer %s', $token) : null,
            ],
        ]);
    }

    public static function getToken(): string
    {
        $client = self::createClient([], [
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json'
            ],
        ]);

        $response = $client->request('POST', '/authentication_token', [
            'body' => json_encode([
                'email' => $_SERVER['ADMIN_EMAIL'],
                'password' => $_SERVER['ADMIN_PASSWORD'],
            ]),
        ]);
        return $response->toArray()['token'];
    }
}
