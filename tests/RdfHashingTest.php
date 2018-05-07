<?php

namespace RdfHash\tests;

use EasyRdf\Graph;
use PHPUnit\Framework\TestCase;
use RdfHash\RdfHashing;

/**
 * Class RdfHashingTest
 * @coversDefaultClass RdfHash\RdfHashing
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
   * @covers ::calculate
   * @covers ::getGraphString
   * @covers ::encodeSubject
   * @covers ::encodeProperties
   * @covers ::encodeObject
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

    /**
     * @test
     * @covers ::calculate
     * @covers ::getGraphString
     * @covers ::encodeSubject
     * @covers ::encodeProperties
     * @covers ::encodeObject
     */
    public function testMoreComplex()
    {
        $base_graph = $this->resource_dir . '/base_graph.ttl';
        $graph = new Graph();
        $graph->parseFile($base_graph, 'turtle');
        $expected_string = file_get_contents($this->resource_dir . '/base_graph.txt');
        $expected_string = preg_replace("~[\r\n\s]~", '', $expected_string);
        $expected_hash = hash('sha256', $expected_string);
        $this->assertEquals(
            $expected_string,
            $this->hasher->getGraphString($graph),
            'Did not get expected string representation'
        );
        $this->assertEquals(
            $expected_hash,
            $this->hasher->calculate($graph),
            'Did not get expected hash value.'
        );
    }

    /**
     * @test
     * @covers ::calculate
     * @covers ::getGraphString
     * @covers ::encodeSubject
     * @covers ::encodeProperties
     * @covers ::encodeObject
     */
    public function testBaseCase()
    {
        $base_graph = $this->resource_dir . '/supersimple.ttl';
        $graph = new Graph();
        $graph->parseFile($base_graph, 'turtle');
        $expected_string = file_get_contents($this->resource_dir . '/supersimple.txt');
        $expected_hash = hash('sha256', $expected_string);
        $this->assertEquals(
            $expected_string,
            $this->hasher->getGraphString($graph),
            'Did not get expected string representation'
        );
        $this->assertEquals(
            $expected_hash,
            $this->hasher->calculate($graph),
            'Did not get expected hash value.'
        );
    }
}
