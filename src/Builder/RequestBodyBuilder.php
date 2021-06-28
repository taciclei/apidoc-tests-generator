<?php
declare(strict_types=1);

namespace PhpJit\ApidocTestsGenerator\Builder;

use ApiPlatform\Core\Api\IriConverterInterface;
use ApiPlatform\Core\Metadata\Resource\Factory\ResourceNameCollectionFactoryInterface;
use ApiPlatform\Core\OpenApi\Factory\OpenApiFactoryInterface;
use ApiPlatform\Core\OpenApi\Model\Components;
use ApiPlatform\Core\OpenApi\Model\MediaType;
use ApiPlatform\Core\OpenApi\Model\Operation;
use ApiPlatform\Core\OpenApi\Model\PathItem;
use Carbon\Carbon;
use Carbon\CarbonInterval;
use Faker\Generator as FakerGenerator;
use ApiPlatform\Core\Identifier\Normalizer\DateTimeIdentifierDenormalizer;
use Symfony\Component\DependencyInjection\ContainerInterface;
use ApiPlatform\Core\Hydra\Serializer\DocumentationNormalizer;


class RequestBodyBuilder implements RequestBodyBuilderInterface
{

    public PathItem $resources;
    public array $doc = [];
    public array $values = [];

    private array $parser;
    private FakerGenerator $fakerGenerator;
    private OpenApiFactoryInterface $openApiFactory;
    private Components $components;
    private DateTimeIdentifierDenormalizer $dateTimeNormalizer;
    public ContainerInterface $container;
    private ResourceNameCollectionFactoryInterface $resourceMetadataFactory;
    private IriConverterInterface $iriConverter;
    private DocumentationNormalizer $itemNormalizer;
    private ResourceNameCollectionFactoryInterface $resourceNameCollection;

    public function __construct(FakerGenerator $fakerGenerator,
                                OpenApiFactoryInterface $openApiFactory,
                                DateTimeIdentifierDenormalizer $dateTimeNormalizer,
                                ContainerInterface $container,
                                ResourceNameCollectionFactoryInterface $resourceNameCollection,
                                IriConverterInterface $iriConverter,
                                DocumentationNormalizer $itemNormalizer
    )
    {
        $this->container = $container;
        $this->iriConverter = $iriConverter;
        $this->resourceNameCollection = $resourceNameCollection;
        $this->fakerGenerator = $fakerGenerator;
        $this->components = $openApiFactory->__invoke()->getComponents();
        $this->dateTimeNormalizer = $dateTimeNormalizer;
        $this->itemNormalizer = $itemNormalizer;

    }

    /**
     * @return array
     */
    public function getResources(): PathItem
    {
        return $this->resources;
    }

    /**
     * @param array $resources
     * @return RequestBodyBuilder
     */
    public function setResources(PathItem $resources): RequestBodyBuilder
    {
        $this->resources = $resources;
        return $this;
    }

    public function getEntityNamespace(string $className): ?string
    {
            $classes = $this->resourceNameCollection->create()->getIterator();
            foreach ($classes as $classe) {
                if (str_contains($classe, $className)) {
                    return $classe;
                }
            }

            return null;
    }


    public function getRequestBody(Operation $operation): self
    {
        $schema = $this->getSchema($operation);
        if (null !== $schema) {
            /* @var $properties \ArrayObject */
            $properties = $this->components->getSchemas()->offsetGet($schema)->offsetGet('properties');

            $this->values = array_diff_key($properties, ["@context" => '', "@id" => '', "@type" => '', "id" => '']);
        }

        return $this;
    }

    public function populateEnum(string $index, array $items, \ArrayObject $value): array
    {
        if ($value->offsetExists('enum')) {
            $items[$index] = $value['enum'][array_rand($value['enum'], 1)];
        }

        return $items;
    }

    public function populateExample(string $index, array $items, \ArrayObject $value): array
    {
        if ($value->offsetExists('example')) {
            $items[$index] = $value['example'];
        }

        return $items;
    }

    public function getBodyInvalid(): array
    {
        $items = [];

        foreach ($this->values as $index => $value) {
            /* @var $value \ArrayObject */

            if ($value->offsetExists('type')) {
                switch ($value->offsetGet('type')) {
                    case "boolean":
                    case "integer":
                    case "number":
                    case "array":
                        $items[$index] = 'fake';
                    default:
                        $items[$index] = false;
                }
            } else {
                //dd($value);
            }
        }
        return $items;
    }

    public function getBody(): array
    {
        $items = [];

        if (is_array($this->values)) {
            foreach ($this->values as $index => $value) {
                if (empty($value)) {
                    $items[$index] = $value;
                    continue;
                }

                $items = $this->populateEnum($index, $items, $value);

                if (!array_key_exists($index, $items)) {
                    $items = $this->populateExample($index, $items, $value);

                    if (!array_key_exists($index, $items)) {
                        $items = $this->getFakerType($index, $items, $value);
                    }

                }

            }

            return $items;
        }
    }

    public function getFakerType(string $index, array $items, \ArrayObject $value): array
    {
        switch ($index) {
            case "email":
                $items[$index] = $this->fakerGenerator->email();
                break;
            case "firstName":
                $items[$index] = $this->fakerGenerator->firstName();
                break;
            case "lastName":
                $items[$index] = $this->fakerGenerator->lastName();
                break;
            case "name":
                $items[$index] = $this->fakerGenerator->name();
                break;
            case "latitude":
                $items[$index] = $this->fakerGenerator->latitude();
                break;
            case "longitude":
                $items[$index] = $this->fakerGenerator->longitude();
                break;
            case "address":
            case "address1":
            case "address2":
                $items[$index] = $this->fakerGenerator->address();
                break;
            case "isbn":
                $items[$index] = $this->fakerGenerator->ean13();
                break;
            case "expiredAt":
                $items[$index] = $this->dateTimeNormalizer->normalize(Carbon::now()
                    ->hours(0)
                    ->minutes(0)
                    ->seconds(0)
                );
                break;
            default:
                $items[$index] = $this->getValueByType($value, $index);
        }

        return $items;
    }

    public function getValueByType(\ArrayObject $value, $index)
    {
        if ($value->offsetExists('type')) {
            switch ($value->offsetGet('type')) {
                case "boolean":
                    return $this->fakerGenerator->boolean();
                case "string":
                    if ($value->offsetExists('format')) {
                        if ($value->offsetGet('format') == 'duration') {
                            return CarbonInterval::days(3)->seconds(32)->format('%rP%yY%mM%dDT%hH%iM%sS');
                        }
                        if ($value->offsetGet('format') == 'date-time') {
                            return $this->dateTimeNormalizer->normalize(Carbon::now()
                                ->hours(0)
                                ->minutes(0)
                                ->seconds(0)
                            );
                        }
                        if ($value->offsetGet('format') == 'uuid') {
                            return $this->fakerGenerator->uuid();
                        }

                        if ($value->offsetGet('format') == 'iri-reference') {
                            //return [$this->getIri($value)];

                        }
                        return $this->fakerGenerator->text(10, 20);
                    }
                    return $this->fakerGenerator->text(10, 20);
                case "email":
                    return $this->fakerGenerator->email();
                case "integer":
                case "number":
                    return $this->fakerGenerator->randomNumber(1);
                case "array":
                    if (isset($value['items']) && !empty($value['items'])) {
                        $value = new \ArrayObject($value['items']);
                        return [];
                        //return [$this->getIri($value)];
                    }
            }
        }
        if ($value->offsetExists('$ref')) {;
            return $this->getIri($value);
        }
    }

    public function getIri(\ArrayObject $value): ?string
    {
        if ($value->offsetExists('$ref')) {
            $schema = explode('/', $value->offsetGet('$ref'));
            $entity = explode('.', end($schema));
                return $this->findIriBy(current($entity), []);
        }
    }

    public function getSchema(Operation $operation, $type = 'application/ld+json'): ?string
    {

        if ($operation->getRequestBody()->getContent()->offsetExists($type)) {
            /* @var $media MediaType */
            $media = $operation->getRequestBody()->getContent()->offsetGet($type);

            if ($media->getSchema()->offsetExists('$ref')) {
                $schema = explode('/', $media->getSchema()->offsetGet('$ref'));
                return end($schema);
            }
            return null;
        }
        return null;
    }

    /**
     * Finds the IRI of a resource item matching the resource class and the specified criteria.
     */
    protected function findIriBy(string $resourceClass, array $criteria): ?string
    {
        $resourceClass = $this->getEntityNamespace($resourceClass);
        if (null !== $resourceClass) {
            if (
                (
                    !$this->container->has('doctrine') ||
                    null === $objectManager = $this->container->get('doctrine')->getManagerForClass($resourceClass)
                ) &&
                (
                    !$this->container->has('doctrine_mongodb') ||
                    null === $objectManager = $this->container->get('doctrine_mongodb')->getManagerForClass($resourceClass)
                )
            ) {
                throw new \RuntimeException(sprintf('"%s" only supports classes managed by Doctrine ORM or Doctrine MongoDB ODM. Override this method to implement your own retrieval logic if you don\'t use those libraries.', __METHOD__));
            }

            $item = $objectManager->getRepository($resourceClass)->findOneBy($criteria);
            if (null === $item) {
                return null;
            }

            return $this->iriConverter->getIriFromItem($item);
        }
        return null;
    }
}
