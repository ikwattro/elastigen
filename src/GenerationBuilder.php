<?php

namespace Ikwattro\ElastiGen;

use Faker\Factory;
use Ikwattro\ElastiGen\Event\DocumentEvent;
use Symfony\Component\EventDispatcher\EventDispatcher;

class GenerationBuilder
{
    private $faker;

    private $eventDispatcher;

    public function __construct(EventDispatcher $eventDispatcher)
    {
        $this->faker = Factory::create();
        $this->eventDispatcher = $eventDispatcher;
    }

    public function map(array &$mapping, array $providers)
    {
        if (!array_key_exists('mappings', $mapping)) {
            throw new \RuntimeException("The mapping provided does not contain the 'mapping' key");
        }

        foreach ($mapping['mappings'] as $type => $info) {
            if (!array_key_exists('properties', $info)) {
                continue;
            }

            foreach ($info['properties'] as $fieldName => $m) {
                $tKey = sprintf('%s.%s', $type, $fieldName);
                if (array_key_exists($tKey, $providers)) {
                    $m['provider'] = $providers[$tKey];
                } elseif (array_key_exists($fieldName, $providers)) {
                    $mapping['mappings'][$type]['properties'][$fieldName]['provider'] = $providers[$fieldName];
                } elseif (isset($m['type']) && 'nested' === $m['type']) {
                    foreach ($m['properties'] as $property => $n) {
                        $nKey = sprintf('%s.%s.%s', $type, $fieldName, $property);
                        $ssKey = sprintf('*.%s.%s', $fieldName, $property);
                        if (array_key_exists($nKey, $providers)) {
                            $n['provider'] = $providers[$nKey];
                            $mapping['mappings'][$type]['properties'][$fieldName]['properties'][$property]['provider'] = $providers[$nKey];
                        } elseif (array_key_exists($ssKey, $providers)) {
                            $mapping['mappings'][$type]['properties'][$fieldName]['properties'][$property]['provider'] = $providers[$ssKey];
                        } elseif (array_key_exists($property, $providers)) {
                            $mapping['mappings'][$type]['properties'][$fieldName]['properties'][$property]['provider'] = $providers[$property];
                        }
                    }
                }
            }
        }

        return $mapping;
    }

    public function generate(array $providedMapping, $max = PHP_INT_MAX)
    {
        $i = 0;
        while ($i < $max) {
            foreach ($providedMapping['mappings'] as $type => $fields) {
                $doc = [];
                foreach ($fields['properties'] as $fieldName => $info) {
                    if (array_key_exists('provider', $info)) {
                        $doc[$fieldName] = $this->getValue($info['provider']);
                    }

                    if (isset($info['type']) && 'nested' === $info['type']) {
                        $subdoc = [];
                        foreach ($info['properties'] as $property => $subInfo) {
                            if (array_key_exists('provider', $subInfo)) {
                                $subdoc[$property] = $this->getValue($subInfo['provider']);
                            }
                            $doc[$fieldName] = $subdoc;
                        }
                    }
                }
                $this->eventDispatcher->dispatch(ElastiGenEvents::DOCUMENT, new DocumentEvent($type, $doc));
                ++$i;
            }
        }
    }

    public function getValue(array $fakerInfo)
    {
        $fakerType = $fakerInfo['type'];
        $format = isset($fakerInfo['format']) ? $fakerInfo['format'] : null;
        if ($fakerType === 'geo' && $format === 'string') {
            $v = sprintf('%s, %s', $this->faker->latitude, $this->faker->longitude);
            return $v;
        }

        return $this->faker->$fakerType;
    }
}