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

class PutTemplateClassItemTest extends ApiTestCase implements TptClassTestInterface {
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
    public function testUpdateTemplateClass(): void
    {
        $body = '{body}';
        $iri = (string) $this->findIriBy(Entity::class, []);
        $this->client->request('PUT', $iri, ['body' => $body]);


        self::assertResponseIsSuccessful();
        self::assertJsonContains((array) json_decode($body));
    }
    /**
     * depends testCreateTemplateClass
     * @group template_class
     */
    public function testCreateInvalidTemplateClass(): void
    {
        $this->markTestIncomplete('Failed asserting that the Response status code is 400');
        $this->markTestSkipped('Failed asserting that the Response status code is 400');

        $iri = (string) $this->findIriBy(Entity::class, []);
        $this->client->request('PUT', $iri, ['json' => [
            'les_invalides' => 'invalid',
        ]]);
        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        //self::assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
        self::assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');

        self::assertJsonContains([
            '@context' => '/contexts/ConstraintViolationList',
            '@type' => 'ConstraintViolationList',
            'hydra:title' => 'An error occurred'
        ]);
    }
}
