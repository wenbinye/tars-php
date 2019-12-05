<?php // -*- php -*-

$finder = PhpCsFixer\Config::create()
        ->getFinder()
        ->notName('config.php');

return PhpCsFixer\Config::create()
    ->setFinder($finder)
    ->setRiskyAllowed(true)
    ->setRules([
        "@Symfony" => true,
        'strict_param' => true,
        'declare_strict_types' => true,
        'array_syntax' => array('syntax' => 'short'),
        'ordered_imports' => true,
        'no_unused_imports' => true,
        'no_alias_functions' => false,
    ]);
