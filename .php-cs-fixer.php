<?php

return (new PhpCsFixer\Config())
    ->setFinder(PhpCsFixer\Finder::create()
        ->exclude('vendor')
        ->in(__DIR__))
    ->setUsingCache(false)
    ->setRules([
        '@Symfony' => true,
        'yoda_style' => false,
        'php_unit_method_casing' => false,
    ]);
