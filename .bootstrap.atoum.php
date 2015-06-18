<?php
$loader = require __DIR__ . '/vendor/autoload.php';

// Pour les annotations Doctrine
\Doctrine\Common\Annotations\AnnotationRegistry::registerLoader(array($loader, 'loadClass'));