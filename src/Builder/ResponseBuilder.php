<?php
declare(strict_types=1);

namespace PhpJit\ApidocTestsGenerator\Builder;

use ApiPlatform\Core\OpenApi\Factory\OpenApiFactoryInterface;
use ApiPlatform\Core\OpenApi\Model\Components;
use ApiPlatform\Core\OpenApi\Model\MediaType;
use ApiPlatform\Core\OpenApi\Model\Operation;
use Faker\Generator as FakerGenerator;
use Symfony\Component\HttpFoundation\Response;
use ApiPlatform\Core\OpenApi\Model\Response as ModelResponse;

class ResponseBuilder implements ResponseBuilderInterface
{

    private FakerGenerator $fakerGenerator;
    private OpenApiFactoryInterface $openApiFactory;
    private Components $components;
    /**
     * RequestBodyBuilder constructor.
     * @param FakerGenerator $fakerGenerator
     * @param OpenApiFactoryInterface $openApiFactory
     */
    public function __construct(FakerGenerator $fakerGenerator, OpenApiFactoryInterface $openApiFactory)
    {
        $this->fakerGenerator = $fakerGenerator;
        $this->components = $openApiFactory->__invoke()->getComponents();
    }

    public function getJsonSchema(Operation $operation, $codeResponse): ?string
    {
        /* @var $properties \ArrayObject */
        $schema = $this->getSchema($operation, $codeResponse);
        if(null !== $schema) {
            $typeSchema = $this->components->getSchemas()->offsetGet($schema);
            return json_encode($typeSchema);
        }
        return null;
    }

    public function getSchema(Operation $operation, int $responseCode = Response::HTTP_OK, string $type = 'application/ld+json'): ?string
    {
        /* @var $response ModelResponse */
        if(array_key_exists($responseCode, $operation->getResponses()))  {
            $response = $operation->getResponses()[$responseCode];
            /* @var $media MediaType */
            if(null !== $response->getContent()) {
            if($response->getContent()->offsetExists($type)) {
                $media = $response->getContent()->offsetGet($type);
                if($media->getSchema()->offsetExists('$ref')) {
                    $schema = explode('/', $media->getSchema()->offsetGet('$ref'));
                    return end($schema);
                }
            }
                echo 'la3';
            //dd($response);
            }
            echo 'la2';
            //dd($response);
        }
        echo 'la1';
        //dd($operation->getResponses());

        return null;
    }
}