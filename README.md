[![travis](https://api.travis-ci.org/whikloj/RdfHashing.svg?branch=master)](https://travis-ci.org/whikloj/RdfHashing)
[![codecov](https://codecov.io/gh/whikloj/RdfHashing/branch/master/graph/badge.svg)](https://codecov.io/gh/whikloj/RdfHashing)



## Introduction

Based off the work of [@barmintor](https://github.com/barmintor)'s [rdf-digest](https://github.com/barmintor/rdf-digest).
RdfHashing is a PHP implementation of the Höfig/Schieferdecker RDF hashing algorithm described in [Hashing of RDF Graphs
and a Solution to the Blank Node Problem](http://ceur-ws.org/Vol-1259/method2014_submission_1.pdf).

It generates a specifically formatted string based on the above paper and then a SHA-256 hash of that string.

### Installation

Install using composer
```bash
composer install
```

### Usage

```php
<?php

use RdfHash\RdfHashing;
use EasyRdf\Graph;

$graph = new Graph();
$graph->parseFile("/some/file/of/RDF.ttl");

$rdf_hash = RdfHashing::calculate($graph);
```

### License

* MIT
 