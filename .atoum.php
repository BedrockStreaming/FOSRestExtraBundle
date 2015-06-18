<?php

ini_set('memory_limit', '2G');

foreach (glob(__DIR__.'/src/*/Tests') as $dir) {
    $runner->addTestsFromDirectory($dir);
}

// Configure code coverage scope
$script->excludeDirectoriesFromCoverage([__DIR__.'/vendor']);