<?php

namespace RdfHash\tests;

use EasyRdf\Graph;
use PHPUnit\Framework\TestCase;
use RdfHash\RdfHashing;

/**
 * Class RdfHashingTest
 * @coversDefaultClass rdfhashing\RdfHashing
 */
class RdfHashingTest extends TestCase
{

    private $resource_dir = __DIR__ . '/resources';
    private $graph_versions = [];

    private $hasher;

    public function setUp()
    {
        $this->graph_versions = [
        ['url' => realpath($this->resource_dir . '/doap.nt'),
        'format' => 'ntriples'],
        ['url' => realpath($this->resource_dir . '/doap.ttl'),
        'format' => 'turtle'],
        ];
        $this->hasher = new RdfHashing();
    }

  /**
   * @test
   */
    public function testHashFunction()
    {

        $unique_hash = null;
        $data = [];
        foreach ($this->graph_versions as $version) {
            //$text_graph = file_get_contents($version['url']);
            $graph = new Graph();
            $graph->parseFile($version['url'], $version['format']);
            $data[$version['url']]['hash'] = $this->hasher->calculate($graph);
        }
        $unique_hash = reset($data)['hash'];
        foreach ($data as $d) {
            $this->assertEquals($unique_hash, $d['hash']);
        }
    }
}
