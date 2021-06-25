<?php
declare(strict_types=1);

namespace PhpJit\ApidocTestsGeneratorTemplateClass;

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;
use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\Client;
use ApiPlatform\Core\Bridge\Symfony\Routing\Router;
use App\Tests\Libs\ClientTrait;
use PhpJit\ApidocTestsGenerator\TptClassTestInterface;

class GetTemplateClassItemTest extends ApiTestCase implements TptClassTestInterface {
    private Client $client;
    private Router $router;

    use ClientTrait;

    protected function setUp(): void
    {

        parent::setUp();
        $this->token = self::getToken();
        $this->client = self::getClient($this->token);
        $router = static::$container->get('api_platform.router');
        if (!$router instanceof Router) {
            throw new \RuntimeException('api_platform.router service not found.');
        }
        $this->router = $router;
    }
    /**
     * depends testCreateTemplateClass
     * @group template_class
     */
    public function testGetTemplateClassItem(): void
    {
        //$this->markTestSkipped();
        $iri = (string) $this->findIriBy(Entity::class, []);
        $this->client->request('GET', $iri);
        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');
/*        self::assertJsonContains([
            '@context' => '/contexts/TemplateClass',
            '@id' => '/template_class',
            '@type' => 'hydra:Item'
        ]);*/
        //static::assertMatchesJsonSchema(file_get_contents(__DIR__.'/template_class.json'));
    }
}
