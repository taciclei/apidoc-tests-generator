<?php
declare(strict_types=1);

namespace PhpJit\ApidocTestsGenerator\TemplateClass;

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;
use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\Client;
use ApiPlatform\Core\Bridge\Symfony\Routing\Router;
use App\Tests\Libs\ClientTrait;
use PhpJit\ApidocTestsGenerator\TptClassTestInterface;

class GetTemplateClassItemTest extends ApiTestCase implements TptClassTestInterface {
    private Client $client;
    private Router $router;

    use ClientTrait;

    protected function setup(): void
    {
        $this->markTestSkipped();
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
     */
    public function testGetTemplateClassCollection(): void
    {
        $this->markTestSkipped();
        $this->client->request('GET', '{route}');
        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');
        self::assertJsonContains([
            '@context' => '/contexts/TemplateClass',
            '@id' => '/template_class',
            '@type' => 'hydra:Item'
        ]);

    }
}
