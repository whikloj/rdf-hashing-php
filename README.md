[![travis](https://api.travis-ci.org/whikloj/rdf-hashing-php.svg?branch=master)](https://travis-ci.org/whikloj/rdf-hashing-php)
[![githubactions](https://github.com/whikloj/rdf-hashing-php/workflows/Build/badge.svg)](https://github.com/whikloj/rdf-hashing-php/actions?query=workflow%3A%22Build%22)
[![codecov](https://codecov.io/gh/whikloj/rdf-hashing-php/branch/master/graph/badge.svg)](https://codecov.io/gh/whikloj/rdf-hashing-php)

## Introduction

Based off the work of [@barmintor](https://github.com/barmintor)'s [rdf-digest](https://github.com/barmintor/rdf-digest).
RdfHashing is a PHP implementation of the Höfig/Schieferdecker RDF hashing algorithm described in [Hashing of RDF Graphs
and a Solution to the Blank Node Problem](http://ceur-ws.org/Vol-1259/method2014_submission_1.pdf).

See also my Java implementation at [rdf-hashing-java](https://github.com/whikloj/rdf-hashing-java)

It generates a specifically formatted string based on the above paper and then a SHA-256 hash of that string.

## Installation

Install using composer
```bash
composer install
```

## Usage

This comes as a small library for inclusion in other applications, but we also include a simple command line
script to test it out

### Library

The RdfHashing class provides two static methods.

* `RdfHashing::calculate(graph)` takes an \EasyRdf\Graph and returns the hexadecimal sha256 hash.
* `RdfHashing::getGraphString(graph)` takes an \EasyRdf\Graph and returns the parsed formatted string of the graph ready to generate the hash.

```php
<?php

use RdfHash\RdfHashing;
use EasyRdf\Graph;

$graph = new Graph();
$graph->parseFile("/some/file/of/RDF.ttl");

$rdf_hash = RdfHashing::calculate($graph);
```

### Command line

A script `rdfhash` allows you to try parse a file or URL to generate a RDF hash.

```bash
> ./rdfhash
You must provide a source path or url

Usage: rdfhash --source
  --source : file or url of rdf source
```

Providing a file path or URL should result in a hash for the found RDF.

```bash
> ./rdfhash --source=./tests/resources/supersimple.ttl                                                                 
c3f2f988a2e339eb6622ba2fe0d6452fffb1b123fed947ba66900d89b6e3ab5c
```

You can also pass the `--debug` argument to see the graph string before it is hashed.

```bash
> ./rdfhash --source=./tests/resources/supersimple.ttl --debug
{*(http://ex#pred[*(http://ex#pred[http://ex#A][http://ex#C])][http://ex#C])}{*(http://ex#pred[*(http://ex#pred[http://ex#B][http://ex#C])][http://ex#C])}{*(http://ex#pred[http://ex#A][http://ex#C])}{*(http://ex#pred[http://ex#B][http://ex#C])}
c3f2f988a2e339eb6622ba2fe0d6452fffb1b123fed947ba66900d89b6e3ab5c
```


### License

* MIT
 
