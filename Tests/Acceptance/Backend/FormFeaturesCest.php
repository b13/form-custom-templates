<?php

declare(strict_types=1);
namespace B13\FormCustomTemplates\Tests\Acceptance\Backend;

use B13\FormCustomTemplates\Tests\Acceptance\Support\BackendTester;

/**
 * Test form extended fields
 */
class FormFeaturesCest
{
    /**
     * Selector for the module container in the topbar
     *
     * @var string
     */
    public static string $mainMenu = '#modulemenu';
    public static string $stage = '#t3-form-stage';
    public static string $inspector = '#t3-form-inspector-panels';
    public static string $inspectorValidators = '#t3-form-inspector-panels .t3-form-validation-errors';
    public static string $formName = 'test-form';
    public static string $topBar = '.t3js-module-docheader';

    /**
     * @param BackendTester $I
     */
    public function _before(BackendTester $I)
    {
        $I->useExistingSession('admin');
        $I->switchToMainFrame();

        $I->click('Forms', self::$mainMenu);
        $I->switchToContentFrame();
        $I->click(self::$formName);
        $I->waitForText(self::$formName, 5, 'h1');
        $I->click('[data-identifier="saveButton"]');
    }

    public function seeTemplateSelectorInFinisher(BackendTester $I): void {
        $finisher = 'div[data-finisher-identifier="EmailToSender"]';
        $I->click('#t3-form-navigation-component-tree-root-container');
        $I->waitForElementVisible($finisher);
        $I->click($finisher . ' a[data-bs-toggle="collapse"]');
        $I->wait(2);

        $actual = $I->grabMultiple('//label/*[contains(text(),"Select email template")]/parent::*/following-sibling::div//select//option');
        $expected = ["Default","Contact template","Shopping cart template"];
        $I->assertEquals($expected, $actual);

        $I->amGoingTo('Prove the selected template was saved');
        $I->selectOption('//label/*[contains(text(),"Select email template")]/parent::*/following-sibling::div//select', 2);
        $I->click('[data-identifier="saveButton"]');

        $I->seeOptionIsSelected('//label/*[contains(text(),"Select email template")]/parent::*/following-sibling::div//select', 'Contact template');
    }

    /**
     * @param BackendTester $I
     * @throws \Exception
     */
    public function seeChangeIdentifier(BackendTester $I): void
    {
        $newIdentifier = 'new-firstname';
        $selectedItem = '//div[@class="ui-sortable-handle t3-form-form-element-selected"]//*[@class="meta-label"]//span[@data-template-property="_identifier"]';
        $identifierInput = '//div[@id="t3-form-inspector"]//label//span[contains(text(),"Change Identifier")]/parent::*/following-sibling::div//input';

        $I->waitForElement(self::$stage);
        $I->click('//div[@class="t3-form-element-info"]//*[contains(text(),"Firstname")]');
        $I->waitForElement(self::$inspector);
        $I->see('Change Identifier', '.t3-form-control-group');

        $I->amGoingTo('See invalid identifier message');
        $I->fillField($identifierInput, 'invalid}');
        $I->waitForText('Not a valid identifier. A valid identifier may contain only a-Z and 0-9 and must not be empty.', 5, self::$inspectorValidators);
        $I->assertNotEquals('invalid}', $I->grabTextFrom($selectedItem));

        $I->amGoingTo('See identifier already in use');
        $I->fillField($identifierInput, 'lastname');
        $I->waitForText('Reset to \'firstname\' because this identifier is already in use.', 5, self::$inspectorValidators);
        $I->assertNotEquals('lastname', $I->grabTextFrom($selectedItem));

        $I->amGoingTo('Changed identifier on stage');
        $I->fillField($identifierInput, $newIdentifier);
        $I->waitForElementNotVisible(self::$inspectorValidators);
        $I->waitForText($newIdentifier, 5, $selectedItem);
        $I->assertEquals($newIdentifier, $I->grabTextFrom($selectedItem));
    }
}
