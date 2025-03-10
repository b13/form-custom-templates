<?php

declare(strict_types=1);

namespace B13\FormCustomTemplates\Tests\Acceptance\Support\Extension;

use Codeception\Event\SuiteEvent;
use Symfony\Component\Mailer\Transport\NullTransport;
use TYPO3\TestingFramework\Core\Acceptance\Extension\BackendEnvironment;
use TYPO3\TestingFramework\Core\Testbase;

/**
 * Load various core extensions and styleguide and call styleguide generator
 */
class BackendFormCustomTemplatesEnvironment extends BackendEnvironment
{
    /**
     * Load a list of core extensions and styleguide
     *
     * @var array
     */
    protected $localConfig = [
        'coreExtensionsToLoad' => [
            'core',
            'beuser',
            'extbase',
            'fluid',
            'backend',
            'frontend',
            'install',
            'form',
        ],
        'testExtensionsToLoad' => [
            'b13/form-custom-templates',
        ],

        'csvDatabaseFixtures' => [
            __DIR__ . '/../../Fixtures/be_users.csv',
            __DIR__ . '/../../Fixtures/pages.csv',
            __DIR__ . '/../../Fixtures/tt_content.csv',
        ],
        'configurationToUseInTestInstance' => [
            'MAIL' => [
                'transport' => NullTransport::class,
            ],
        ],
        'pathsToLinkInTestInstance' => [
            'typo3conf/ext/form_custom_templates/Tests/Acceptance/Fixtures/sites' =>
            'typo3conf/sites',
        ],
    ];

    public function bootstrapTypo3Environment(SuiteEvent $suiteEvent)
    {
        parent::bootstrapTypo3Environment($suiteEvent);
        // for local
        // mkdir -p .Build/Web/fileadmin/form_definitions && cp Tests/Acceptance/Fixtures/form_definitions/test-form.form.yaml .Build/Web/fileadmin/form_definitions/
        mkdir(ORIGINAL_ROOT . 'typo3temp/var/tests/acceptance/fileadmin/form_definitions');
        // Copy form fixture into place
        copy(ORIGINAL_ROOT . '../../Tests/Acceptance/Fixtures/form_definitions/test-form.form.yaml', ORIGINAL_ROOT . 'typo3temp/var/tests/acceptance/fileadmin/form_definitions/test-form.form.yaml');
    }
}
