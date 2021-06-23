<?php

declare(strict_types=1);

namespace PhpJit\ApidocTestsGenerator\Traits;

use ApiPlatform\Core\OpenApi\Model\Components;
use ApiPlatform\Core\OpenApi\Model\MediaType;
use ApiPlatform\Core\OpenApi\Model\Operation;
use Carbon\Carbon;
use Carbon\CarbonInterval;

trait SwaggerTrait
{
    public array $resources = [];
    public array $doc = [];

    public function getResources(string $className): string
    {
        return $this->resources[$className]->getName();
    }

    public function fetchResources()
    {
        $this->entityManager = self::bootKernel()->getContainer()
            ->get('doctrine')
            ->getManager();

        $metas = $this->entityManager->getMetadataFactory()->getAllMetadata();

        foreach ($metas as $meta) {
            $array = explode('\\', $meta->getName());
            $name = end($array);
            $this->resources[$name] = $meta;
        }
    }

    public function getRequestBody(array $params, Components $components): ?array
    {
        try {
            $schema = $this->getSchema($params['operation']);
            if ($schema !== null) {
                /* @var $properties \ArrayObject */
                $properties = $components->getSchemas()->offsetGet($schema)->offsetGet('properties');

                $toto = $this->denormalizer->normalize($properties);
                echo 'plop';
                //dd($toto);
                $values = array_diff_key($properties, ["@context" => '', "@id" => '', "@type" => '', "id" => '']);

                return $this->getBody($values);
            }
            return null;
        } catch (\Exception $exception) {
            dd($exception);
        }
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

    public function getBody(array $values, array $required = []): array
    {
        $items = [];

        foreach ($values as $index => $value) {
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
            default:
                //if($this->actualUri =='')
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
                            return (string)Carbon::now()
                                ->hours(0)
                                ->minutes(0)
                                ->seconds(0)
                                ->format(\DateTimeInterface::ISO8601);
                        }
                        if ($value->offsetGet('format') == 'uuid') {
                            return $this->fakerGenerator->uuid();
                        }

                        if ($value->offsetGet('format') == 'iri-reference') {
                            //$res = $this->getResources('Endowment');
                            //$iri = $this->findIriBy($res, []);
                            echo 'iri-referenc';
                            //dd($iri);

                        }
                        return $this->fakerGenerator->text(10, 20);
                    }
                    return $this->fakerGenerator->text(10, 20);
                case "email":
                    return $this->fakerGenerator->email();
                case "integer":
                case "number":
                    return $this->fakerGenerator->randomNumber(3, 10);
                case "array":
                    if (isset($value['items']) && !empty($value['items'])) {
                        $val = new \ArrayObject($value['items']);
                        return [];
                        //return [$this->getValueByType($val, $index)];
                    }
                    echo 'ici';
                    dd($value);
                default:
                    echo 'def';
                    dd($value);
            }
        }
        //echo  'val : '.$this->actualUri;
        //dd($value);
    }

    public function getSchema(Operation $operation, $type = 'application/ld+json'): ?string
    {
        if ($operation->getRequestBody()->getContent()->offsetExists($type)) {
            /* @var $media MediaType */
            $media = $operation->getRequestBody()->getContent()->offsetGet($type);

            if ($media->getSchema()->offsetExists('$ref')) {
                $schema = explode('/', $media->getSchema()->offsetGet('$ref'));
                return end($schema);
            } else {
                echo '$ref';
                dd($media->getSchema()->offsetExists('$ref'));
            }
        } else {
            echo $type;
            return null;
            //dd($operation->getRequestBody()->getContent());
        }
    }
}