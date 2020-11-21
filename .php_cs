<?php

return PhpCsFixer\Config::create()
    ->setRules([
        '@Symfony' => true,
        'yoda_style' => false,
    ])
    ->setFinder(PhpCsFixer\Finder::create()
        ->exclude('vendor')
        ->in(__DIR__));
