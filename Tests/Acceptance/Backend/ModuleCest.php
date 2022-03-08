<?php

declare(strict_types=1);
namespace B13\FormCustomTemplates\Tests\Acceptance\Backend;

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

use B13\FormCustomTemplates\Tests\Acceptance\Support\BackendTester;

/**
 * Tests the styleguide backend module can be loaded
 */
class ModuleCest
{
    /**
     * Selector for the module container in the topbar
     *
     * @var string
     */
    public static $topBarModuleSelector = '#modulemenu';

    /**
     * @param BackendTester $I
     */
    public function _before(BackendTester $I)
    {
        $I->useExistingSession('admin');
        $I->switchToIFrame();
        $I->makeScreenshot('typo3Version.png');
    }

    /**
     * @param BackendTester $I
     */
    public function styleguideInTopbarHelpCanBeCalled(BackendTester $I): void
    {
        $I->see('New TYPO3 site');
    }
}
