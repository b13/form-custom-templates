<?php

declare(strict_types=1);

namespace B13\FormCustomTemplates\Tests\Acceptance\Backend;

use B13\FormCustomTemplates\Tests\Acceptance\Support\BackendTester;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Test form extended fields
 */
class FormFeaturesCest
{
    public static string $mainMenu = '#modulemenu';
    public static string $stage = 'section[data-identifier="stageContainer"]';
    public static string $formName = 'test-form';

    /**
     * @param BackendTester $I
     */
    public function _before(BackendTester $I)
    {
        $I->loginAs('admin');

        $I->switchToMainFrame();

        $I->click('Forms', self::$mainMenu);
        $I->switchToContentFrame();
        $I->waitForText(self::$formName);
        $I->click(self::$formName);
        $I->waitForText(self::$formName, 5, 'h1');
    }

    public function seeTemplateSelectorInFinisher(BackendTester $I): void
    {
        $finisher = 'div[data-finisher-identifier="EmailToSender"]';
        $I->click('span[data-identifier="treeRootElement"]');
        $I->waitForElementVisible($finisher);
        $typo3Version = (GeneralUtility::makeInstance(Typo3Version::class))->getMajorversion();
        $finisherClick = 'button[data-bs-toggle="collapse"]';
        if ($typo3Version < 13) {
            $finisherClick = 'a[data-bs-toggle="collapse"]';
        }
        $I->click($finisher . ' ' . $finisherClick);

        $I->waitForText('Select email template');
        $actual = $I->grabMultiple('//label/*[contains(text(),"Select email template")]/parent::*/following-sibling::div//select//option');
        $expected = ['No template', 'Contact template', 'Shopping cart template'];
        $I->assertEquals($expected, $actual);

        $I->amGoingTo('Prove the selected template was saved');
        $I->selectOption('//label/*[contains(text(),"Select email template")]/parent::*/following-sibling::div//select', 2);

        $I->click('[data-identifier="saveButton"]');
        $I->waitForElementVisible($finisher);
        $I->wait(1);
        $I->click($finisher . ' ' . $finisherClick);
        $I->waitForText('Select email template');
        $I->seeOptionIsSelected('//label/*[contains(text(),"Select email template")]/parent::*/following-sibling::div//select', 'Contact template');
    }

    /**
     * @param BackendTester $I
     * @throws \Exception
     */
    public function seeChangeIdentifier(BackendTester $I): void
    {
        $newIdentifier = 'new-firstname';
        $identifierInput = '//div[@data-identifier="inspector"]//label//span[contains(text(),"Change Identifier")]/parent::*/following-sibling::div//input';
        $inspector = 'div[data-identifier="inspector"]';
        $typo3Version = (GeneralUtility::makeInstance(Typo3Version::class))->getMajorversion();
        $selectorPrefix = 'formeditor';
        if ($typo3Version < 13) {
            $selectorPrefix = 't3-form';
        }
        $inspectorValidators = $inspector . ' .' . $selectorPrefix . '-validation-errors';

        $I->waitForElement(self::$stage);
        $I->click('//div[@class="'. $selectorPrefix . '-element-info"]//*[contains(text(),"Firstname")]');
        $I->waitForElement($inspector);
        $I->see('Change Identifier', $inspector);

        $I->amGoingTo('See invalid identifier message');
        $I->fillField($identifierInput, 'invalid}');
        $I->waitForText('Not a valid identifier. A valid identifier may contain only a-Z and 0-9 and must not be empty.', 5, $inspectorValidators);
        $I->assertNotEquals('invalid}', $I->grabTextFrom($identifierInput));

        $I->amGoingTo('See identifier already in use');
        $I->fillField($identifierInput, 'lastname');
        $I->waitForText('Reset to \'firstname\' because this identifier is already in use.', 5, $inspectorValidators);
        $I->assertNotEquals('lastname', $I->grabTextFrom($identifierInput));

        $I->amGoingTo('Changed identifier on stage');
        $I->fillField($identifierInput, $newIdentifier);
        $I->waitForElementNotVisible($inspectorValidators);
        $I->waitForText($newIdentifier);
    }
}
