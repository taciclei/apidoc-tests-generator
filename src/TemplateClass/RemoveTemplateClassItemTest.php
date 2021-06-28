<?php
declare(strict_types=1);

namespace PhpJit\ApidocTestsGenerator\TemplateClass;

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;
use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\Client;
use ApiPlatform\Core\Bridge\Symfony\Routing\Router;
use Hautelook\AliceBundle\PhpUnit\ReloadDatabaseTrait;
use PhpJit\ApidocTestsGenerator\TptClassTestInterface;
use PhpJit\ApidocTestsGenerator\Traits\ClientTrait;
use Symfony\Component\HttpFoundation\Response;

class RemoveTemplateClassItemTest extends ApiTestCase implements TptClassTestInterface
{
    use ClientTrait, ReloadDatabaseTrait;
    private Client $client;
    private Router $router;
    private string $token;

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
        $iri = (string) $this->findIriBy(Entity::class, []);
        $this->client->request('DELETE', $iri);

        self::assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);
    }
}
