<?php
declare(strict_types=1);

namespace PhpJit\ApidocTestsGenerator\TemplateClass;

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;
use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\Client;
use ApiPlatform\Core\Bridge\Symfony\Routing\Router;
use PhpJit\ApidocTestsGenerator\TptClassTestInterface;
use PhpJit\ApidocTestsGenerator\Traits\ClientTrait;
use Symfony\Component\HttpFoundation\Response;

class PutTemplateClassItemTest extends ApiTestCase implements TptClassTestInterface {
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
     * depends testCreateTemplateClass
     * @group template_class
     */
    public function testReplaceTemplateClass(): void
    {
        $body = '{body}';
        $iri = (string) $this->findIriBy(Entity::class, []);
        $this->client->request('PUT', $iri, ['body' => $body]);


        self::assertResponseIsSuccessful();
        //self::assertJsonContains((array) json_decode('{body}'));
    }
    /**
     * depends testCreateTemplateClass
     * @group template_class
     */
    public function testCreateInvalidTemplateClass(): void
    {

        $iri = (string) $this->findIriBy(Entity::class, []);
        $body = '{body_invalid}';
        $this->client->request('PUT', $iri, ['body' =>$body]);

        self::assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        self::assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');

        self::assertJsonContains([
            '@context' => '/contexts/Error',
            '@type' => 'hydra:Error'
        ]);
    }
}
