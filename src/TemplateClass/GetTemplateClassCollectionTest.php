<?php
declare(strict_types=1);

namespace PhpJit\ApidocTestsGenerator\TemplateClass;

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;
use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\Client;
use ApiPlatform\Core\Bridge\Symfony\Routing\Router;
use App\Tests\Api\RefreshDatabaseTrait;
use PhpJit\ApidocTestsGenerator\TptClassTestInterface;

class GetTemplateClassCollectionTest extends ApiTestCase implements TptClassTestInterface {
    private Client $client;
    private Router $router;

    use RefreshDatabaseTrait;

    protected function setup(): void
    {
        $this->client = static::createClient();
        $router = static::$container->get('api_platform.router');
        if (!$router instanceof Router) {
            throw new \RuntimeException('api_platform.router service not found.');
        }
        $this->router = $router;
    }
    /**
     * @depends testCreateTemplateClass
     * @group template_class
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function testGetTemplateClassCollection(): void
    {
        $this->client->request('GET', '/template_class');
        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');
        self::assertJsonContains([
            '@context' => '/contexts/TemplateClass',
            '@id' => '/template_class',
            '@type' => 'hydra:Collection'
        ]);

        static::assertMatchesJsonSchema(file_get_contents(__DIR__.'/schemas/template_class.json'));
        self::assertMatchesResourceCollectionJsonSchema(TemplateClass::class);
    }
}
