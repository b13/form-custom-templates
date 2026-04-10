<?php

declare(strict_types=1);

namespace B13\FormCustomTemplates\Tests\Acceptance\Support;

use B13\FormCustomTemplates\Tests\Acceptance\Support\_generated\BackendTesterActions;
use Codeception\Util\Locator;
use TYPO3\TestingFramework\Core\Acceptance\Step\FrameSteps;

/**
 * Default backend admin or editor actor in the backend
*/
class BackendTester extends \Codeception\Actor
{
    use BackendTesterActions;
    use FrameSteps;

    public function loginAs(string $username): void
    {
        $I = $this;

        $I->amOnPage('/typo3');
        $I->waitForElement('body[data-typo3-login-ready]');
        // logging in
        $I->amOnPage('/typo3');
        $I->submitForm('#typo3-login-form', [
            'username' => $username,
            'p_field' => 'password',
        ]);
        $I->waitForElement('iframe[name="list_frame"]', 2);
        $I->switchToIFrame('list_frame');
        $I->waitForElement(Locator::firstElement('div.module'));
        $I->switchToIFrame();
    }

    /**
     * v14: Click a content element in the page module to open it in the context panel,
     * then switch into the context panel iframe.
     */
    public function openRecordInContextPanelOrWithEditDocumentController(int $uid): void
    {
        $this->waitForElement('#element-tt_content-' . $uid . ' typo3-backend-contextual-record-edit-trigger');
        $this->click('#element-tt_content-' . $uid . ' typo3-backend-contextual-record-edit-trigger');
        $this->switchToMainFrame();
        $this->waitForElement('iframe[name="modal_frame"]', 10);
        $this->switchToIFrame('modal_frame');
        $this->waitForElementNotVisible('#t3js-ui-block');
        $this->click('a.t3js-contextual-fullscreen');
        $this->switchToMainFrame();
        $this->switchToContentFrame();
        $this->waitForElement('#EditDocumentController');
    }
}
