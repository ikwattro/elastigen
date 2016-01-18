<?php

namespace Ikwattro\ElastiGen;

use Elasticsearch\ClientBuilder;
use Ikwattro\ElastiGen\Event\DocumentEvent;

class Indexer
{
    protected $esClient;

    protected $index;

    protected $jobs = [];

    public function __construct($hostUrl, $index)
    {
        $hosts = array($hostUrl);
        $this->esClient = ClientBuilder::create()
            ->setHosts($hosts)
            ->setRetries(2)
            ->build();

        $this->index = $index;
        $this->jobs = ['body' => []];
    }

    public function onDocument(DocumentEvent $doc)
    {
        $this->jobs['body'][] = [
            'index' => [
                '_index' => $this->index,
                '_type' => $doc->getType()
            ]
        ];
        $this->jobs['body'][] = $doc->getFields();

        if (count($this->jobs['body']) % 1000 === 0) {
            $this->esClient->bulk($this->jobs);
            $this->jobs = ['body' => []];
        }
    }
}