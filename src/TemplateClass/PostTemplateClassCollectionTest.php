<?php
declare(strict_types=1);

namespace PhpJit\ApidocTestsGenerator\TemplateClass;

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;
use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\Client;
use ApiPlatform\Core\Bridge\Symfony\Routing\Router;
use App\Tests\Libs\ClientTrait;
use PhpJit\ApidocTestsGenerator\TptClassTestInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class PostTemplateClassCollectionTest extends ApiTestCase implements TptClassTestInterface {
    private Client $client;
    private Router $router;

    use ClientTrait;

    public static function getClient(): HttpClientInterface
    {
        return self::createClient([], [
            'headers' => [
                'Accept' => 'application/ld+json',
                'Content-Type' => 'application/ld+json'
            ],
        ]);
    }

    protected function setup(): void
    {
        $this->client = static::getClient();
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
        self::assertJsonContains($body);
        self::assertMatchesRegularExpression('~^/template_class/[0-9a-fA-F]{8}\-[0-9a-fA-F]{4}\-[0-9a-fA-F]{4}\-[0-9a-fA-F]{4}\-[0-9a-fA-F]{12}$~', $response->toArray()['@id']);
        self::assertMatchesResourceItemJsonSchema(Entity::class);
    }

    /**
     * @group template_class
     */
    public function testCreateInvalidTemplateClass(): void
    {
        $this->client->request('POST', '/template_class', ['json' => [
            'les_invalides' => 'invalid',
        ]]);

        self::assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
        self::assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');

        self::assertJsonContains([
            '@context' => '/contexts/ConstraintViolationList',
            '@type' => 'ConstraintViolationList',
            'hydra:title' => 'An error occurred'
        ]);
    }
}
