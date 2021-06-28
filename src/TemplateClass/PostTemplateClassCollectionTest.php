<?php
declare(strict_types=1);

namespace PhpJit\ApidocTestsGenerator\TemplateClass;

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;
use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\Client;
use ApiPlatform\Core\Bridge\Symfony\Routing\Router;
use PhpJit\ApidocTestsGenerator\TptClassTestInterface;
use PhpJit\ApidocTestsGenerator\Traits\ClientTrait;
use Symfony\Component\HttpFoundation\Response;

class PostTemplateClassCollectionTest extends ApiTestCase implements TptClassTestInterface
{
    use ClientTrait;
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
     */
    public function testCreateTemplateClass(): void
    {
        $body = '{body}';

        $response = $this->client->request('POST', '{route}', ['body' => $body]);

        self::assertResponseStatusCodeSame(Response::HTTP_CREATED);
        self::assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');
        //self::assertJsonContains('{body}');
        self::assertMatchesRegularExpression('~^{route}/[0-9a-fA-F]{8}\-[0-9a-fA-F]{4}\-[0-9a-fA-F]{4}\-[0-9a-fA-F]{4}\-[0-9a-fA-F]{12}$~', $response->toArray()['@id']);
        self::assertMatchesResourceItemJsonSchema(Entity::class);
    }

    /**
     * @group template_class
     */
    public function testCreateInvalidTemplateClass(): void
    {
        $body = '{body_invalid}';
        $this->client->request('POST', '{route}', ['body' => $body]);

        self::assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        self::assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');

/*        self::assertJsonContains([
            '@context' => '/contexts/ConstraintViolationList',
            '@type' => 'ConstraintViolationList',
            'hydra:title' => 'An error occurred'
        ]);*/
    }
}
