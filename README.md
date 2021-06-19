# ApiDoc Tests Generator

This PHP tool can generate ApiTestCase test classes for your ApiDoc.

## Install

```console
$ composer require --dev phpjit/apidoc-tests-generator
```

## Generate Test Class
```console
/srv/api # rm -rf tests/Func/Auto  &&  bin/console generate-test-class
Generating test class for PhpJit\ApidocTestsGenerator\TemplateClass\GetTemplateClassItemTest
```
```php
<?php
declare(strict_types=1);

namespace Test\Func\Auto\Books;

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;
use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\Client;
use ApiPlatform\Core\Bridge\Symfony\Routing\Router;
use App\Tests\Api\RefreshDatabaseTrait;
use PhpJit\ApidocTestsGenerator\TptClassTestInterface;

class GetBooksItemTest extends ApiTestCase implements TptClassTestInterface {
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

    public function testGetBooksCollection(): void
    {
        $this->client->request('GET', '/books/1');
        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');
        self::assertJsonContains([
            '@context' => '/contexts/Books',
            '@id' => '/books',
            '@type' => 'hydra:Item'
        ]);

    }
}
```
```console
Test class written to /srv/api/tests/Func/Auto/Books/GetBooksItemTest.php
Generating test class for PhpJit\ApidocTestsGenerator\TemplateClass\GetTemplateClassCollectionTest
```

fork by jwage/phpunit-test-generator
