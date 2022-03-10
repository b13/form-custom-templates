<?php

declare(strict_types=1);
namespace B13\FormCustomTemplates\Tests\Acceptance\Backend;

use B13\FormCustomTemplates\Tests\Acceptance\Support\BackendTester;

/**
 * Test form extended fields
 */
class PluginFormCest
{
    /**
     * @param BackendTester $I
     */
    public function _before(BackendTester $I)
    {
        $I->useExistingSession('admin');
        $I->switchToMainFrame();

        $I->waitForElement('#identifier-0_1 .chevron');
        $I->click('#identifier-0_1 .chevron');
        $I->click('#identifier-0_1 .node-name');
    }

    /**
     * @param BackendTester $I
     * @throws \Exception
     */
    public function seeOverrideTemplateOptions(BackendTester $I): void
    {
        $I->switchToContentFrame();

        $I->click('MyForm');
        $I->waitForText('Plugin');
        $I->click('Plugin');
        $I->waitForText('Form definition');

        $I->click('Email to sender (form submitter)');
        $I->waitForText('Select email template');
        $I->waitForElement('//label[contains(text(),"Select email template")]');
        $I->scrollTo('//label[contains(text(),"Select email template")]');

        $I->amGoingTo('See a list of expected email template pages in select of form plugin override');
        $actual = $I->grabMultiple('//label[contains(text(),"Select email template")]/following-sibling::div//select//option');
        $expected = ["Default [[Empty]]","Contact template [2]","Shopping cart template [3]"];
        $I->assertEquals($expected, $actual);
    }

    /**
     * @param BackendTester $I
     * @return void
     * @throws \Exception
     */
    public function seePageTypeElements(BackendTester $I): void
    {
        $I->amGoingTo('See if there is a drag icon in the pageTree\'s top bar');
        $I->seeElement('#typo3-pagetree-toolbar div[title="Email template"]');

        $I->amGoingTo('See if icon was applied to page in page tree');
        $I->waitForElement('#icon-page-email');
    }
}
