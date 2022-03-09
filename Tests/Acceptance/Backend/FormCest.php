<?php

declare(strict_types=1);
namespace B13\FormCustomTemplates\Tests\Acceptance\Backend;

use B13\FormCustomTemplates\Tests\Acceptance\Support\BackendTester;

/**
 * Test form extended fields
 */
class FormCest
{
    /**
     * Selector for the module container in the topbar
     *
     * @var string
     */
    public static string $mainMenu = '#modulemenu';
    public static string $topBar = '.t3js-module-docheader';
    public static string $modalBody = '.t3js-modal-body';
    public static string $formName = 'form for templates';

    /**
     * @param BackendTester $I
     */
    public function _before(BackendTester $I)
    {
        $I->useExistingSession('admin');
        $I->switchToIFrame();
        $I->click('Forms', self::$mainMenu);
    }

    /**
     * @param BackendTester $I
     * @throws \Exception
     */
    public function createForm(BackendTester $I): void
    {
        $I->waitForElementNotVisible('#nprogress');
        $I->switchToContentFrame();
        $I->waitForText('Form Management');
        $I->click('Create new form');
        $I->wait(3);
        $I->switchToMainFrame();
        $I->click('//button[contains(text(),"Blank form")]');
        $I->waitForElement('#new-form-name');
        $I->fillField('#new-form-name', self::$formName);
        $I->click('button[name=next]');
        $I->waitForText('Check and confirm');
        $I->click('button[name=next]');
        $I->switchToContentFrame();
        $I->waitForText(self::$formName, 5, 'h1');
    }
}
