<?php
declare(strict_types=1);

namespace PhpJit\ApidocTestsGeneratorTemplateClass;

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;
use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\Client;
use ApiPlatform\Core\Bridge\Symfony\Routing\Router;
use App\Tests\Libs\ClientTrait;
use App\Tests\Libs\RefreshDatabaseTrait;
use PhpJit\ApidocTestsGenerator\TptClassTestInterface;
use Symfony\Component\HttpFoundation\Response;

class DeleteTemplateClassItemTest extends ApiTestCase implements TptClassTestInterface {
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
     * @group template_class
     * depends testCreateTemplateClass
     */
    public function testDeleteTemplateClassItem(): void
    {
        //$this->markTestSkipped();
        $iri = (string) $this->findIriBy(Entity::class, []);
        $this->client->request('DELETE', $iri);

        self::assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);
        self::assertNull(
            static::$container->get('doctrine')->getRepository(Entity::class)->findOneBy([])
        );
    }
}
