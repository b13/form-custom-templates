<?php

declare(strict_types=1);

return [
    'frontend' => [
        'b13/ext-form-custom-templates' => [
            'target' => \B13\FormCustomTemplates\Middleware\EmailPagePreviewGuard::class,
            'after' => [
                'typo3/cms-frontend/tsfe',
            ],
            'before' => [
                'typo3/cms-frontend/prepare-tsfe-rendering',
            ],
        ],
    ],
];
