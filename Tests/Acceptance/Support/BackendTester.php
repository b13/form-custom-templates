<?php

declare(strict_types=1);
namespace B13\FormCustomTemplates\Tests\Acceptance\Support;

use B13\FormCustomTemplates\Tests\Acceptance\Support\_generated\BackendTesterActions;
use TYPO3\TestingFramework\Core\Acceptance\Step\FrameSteps;

/**
 * Default backend admin or editor actor in the backend
*/
class BackendTester extends \Codeception\Actor
{
    use BackendTesterActions;
    use FrameSteps;
}
