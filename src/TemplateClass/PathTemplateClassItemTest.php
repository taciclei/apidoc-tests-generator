<?php
declare(strict_types=1);

namespace PhpJit\ApidocTestsGenerator\TemplateClass;

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;
use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\Client;
use ApiPlatform\Core\Bridge\Symfony\Routing\Router;
use App\Tests\Api\RefreshDatabaseTrait;
use PhpJit\ApidocTestsGenerator\TptClassTestInterface;
use Symfony\Component\HttpFoundation\Response;

class PathTemplateClassItemTest extends ApiTestCase implements TptClassTestInterface {
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
     * @group template_class
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function testDeleteTemplateClassItem(): void
    {
        $client = static::createClient();
        $iri = (string) $this->findIriBy(TemplateClass::class, ['id' => '1']);
        $client->request('DELETE', $iri);

        self::assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);
        self::assertNull(
            static::$container->get('doctrine')->getRepository(TemplateClass::class)->findOneBy(['id' => 'id'])
        );
    }
}
