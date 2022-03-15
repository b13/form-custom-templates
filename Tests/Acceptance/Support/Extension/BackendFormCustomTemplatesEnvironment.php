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
            'filelist',
            'extensionmanager',
            'setup',
            'backend',
            'belog',
            'install',
            'impexp',
            'frontend',
            'recordlist',
            'redirects',
            'reports',
            'sys_note',
            'scheduler',
            'tstemplate',
            'lowlevel',
            'dashboard',
            'workspaces',
            'info',
            'fluid_styled_content',
            'indexed_search',
            'adminpanel',
            'form',
            'felogin',
            'seo',
            'recycler',
        ],
        'testExtensionsToLoad' => [
            'typo3conf/ext/form_custom_templates',
        ],
        'xmlDatabaseFixtures' => [
            'PACKAGE:typo3/testing-framework/Resources/Core/Acceptance/Fixtures/be_users.xml',
            'PACKAGE:../Web/typo3conf/ext/form_custom_templates/Tests/Acceptance/Fixtures/be_sessions.xml',
            'PACKAGE:typo3/testing-framework/Resources/Core/Acceptance/Fixtures/be_groups.xml',
            'PACKAGE:../Web/typo3conf/ext/form_custom_templates/Tests/Acceptance/Fixtures/pages.xml',
            'PACKAGE:../Web/typo3conf/ext/form_custom_templates/Tests/Acceptance/Fixtures/sys_template.xml',
            'PACKAGE:../Web/typo3conf/ext/form_custom_templates/Tests/Acceptance/Fixtures/tt_content.xml',
        ],
        'configurationToUseInTestInstance' => [
            'MAIL' => [
                'transport' => NullTransport::class,
            ],
        ],
        'pathsToLinkInTestInstance' => [
           'typo3conf/ext/form_custom_templates/Tests/Acceptance/Fixtures/sites' =>
           'typo3conf/sites',
        ]
    ];

    public function bootstrapTypo3Environment(SuiteEvent $suiteEvent)
    {
        $bootstrap = parent::bootstrapTypo3Environment($suiteEvent);
        $testbase = new Testbase();
        $testbase->createDirectory(ORIGINAL_ROOT . 'typo3temp/var/tests/acceptance/fileadmin/form_definitions');
        // Copy form fixture into place
        copy(ORIGINAL_ROOT . 'typo3conf/ext/form_custom_templates/Tests/Acceptance/Fixtures/form_definitions/test-form.form.yaml', ORIGINAL_ROOT . 'typo3temp/var/tests/acceptance/fileadmin/form_definitions/test-form.form.yaml');

        return $bootstrap;
    }
}
