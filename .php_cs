<?php

return PhpCsFixer\Config::create()
    ->setRules([
        '@Symfony' => true,
        'yoda_style' => false,
        'php_unit_method_casing' => false,
    ])
    ->setFinder(PhpCsFixer\Finder::create()
        ->exclude('vendor')
        ->in(__DIR__));
