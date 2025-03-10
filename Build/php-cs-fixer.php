<?php

declare(strict_types=1);

$config = \TYPO3\CodingStandards\CsFixerConfig::create();
$config->getFinder()->exclude(['var'])->in(__DIR__ . '/..');
$config->setRules([
    'nullable_type_declaration' => [
        'syntax' => 'question_mark',
    ],
    'nullable_type_declaration_for_default_null_value' => true,
    'declare_strict_types' => true,
]);
return $config;
