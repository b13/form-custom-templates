<?php

declare(strict_types=1);

namespace B13\FormCustomTemplates\Tests\Acceptance\Backend;

use B13\FormCustomTemplates\Tests\Acceptance\Support\BackendTester;
use B13\FormCustomTemplates\Tests\Acceptance\Support\PageTree;
use B13\FormCustomTemplates\Tests\Acceptance\Support\PageTreeV13;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Utility\GeneralUtility;

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
        $I->loginAs('admin');
    }

    /**
     * @param BackendTester $I
     * @throws \Exception
     */
    public function seeOverrideTemplateOptions(BackendTester $I, PageTree $pageTree, PageTreeV13 $pageTreeV13): void
    {
        $I->click('Page');
        if (GeneralUtility::makeInstance(Typo3Version::class)->getMajorVersion() < 13) {
            $I->waitForElement('#typo3-pagetree-tree .nodes .node');
            $pageTree->openPath(['Root']);
        } else {
            $pageTreeV13->openPath(['Root']);
        }
        $I->wait(0.5);
        $I->switchToContentFrame();

        $I->click('MyForm');
        $I->waitForText('Plugin');
        $I->click('Plugin');
        $I->waitForText('Form definition');
        $I->wait(10);
        $I->click('Email to sender (form submitter)');
        $I->waitForText('Select email template');
        $I->waitForElement('//label[contains(text(),"Select email template")]');
        $I->scrollTo('//label[contains(text(),"Select email template")]');

        $I->amGoingTo('See a list of expected email template pages in select of form plugin override');
        $actual = $I->grabTextFrom('//label[contains(text(),"Select email template")]/following-sibling::div//select');

        $I->assertStringContainsString('Contact template', $actual);
        $I->assertStringContainsString('Shopping cart template', $actual);
    }

    /**
     * @param BackendTester $I
     * @throws \Exception
     */
    public function seePageTypeElements(BackendTester $I, PageTree $pageTree, PageTreeV13 $pageTreeV13): void
    {
        $I->click('Page');
        if (GeneralUtility::makeInstance(Typo3Version::class)->getMajorVersion() < 13) {
            $I->waitForElement('#typo3-pagetree-tree .nodes .node');
            $pageTree->openPath(['Root']);
        } else {
            $pageTreeV13->openPath(['Root']);
        }
        $I->amGoingTo('See if there is a drag icon in the pageTree\'s top bar');
        $I->waitForElement('#typo3-pagetree-toolbar');
        $I->seeElement('#typo3-pagetree-toolbar div[data-node-type="125"]');

        $I->amGoingTo('See if icon was applied to page in page tree');
        $I->waitForElement('typo3-backend-icon[identifier="apps-pagetree-page-email"]');
    }
}
