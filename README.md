![travis](https://api.travis-ci.org/whikloj/RdfHashing.svg?branch=master)

## Introduction

Based off the work of [@barmintor](https://github.com/barmintor)'s [rdf-digest](https://github.com/barmintor/rdf-digest).
RdfHashing is a PHP implementation of the Höfig/Schieferdecker RDF hashing algorithm described in [Hashing of RDF Graphs
and a Solution to the Blank Node Problem](http://ceur-ws.org/Vol-1259/method2014_submission_1.pdf)

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

$rdf = new RdfHashing();
$rdf_hash = $rdf->calculate($graph);
```

### License

* MIT
 