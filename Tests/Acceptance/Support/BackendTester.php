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
}
