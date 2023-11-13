<?php

declare(strict_types=1);

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

namespace TYPO3\CMS\Core\Tests\Acceptance\Application\Info;

use Codeception\Example;
use TYPO3\CMS\Core\Tests\Acceptance\Support\ApplicationTester;
use TYPO3\CMS\Core\Tests\Acceptance\Support\Helper\PageTree;

/**
 * Tests concerning Reports Module
 */
final class InfoModuleCest
{
    public function _before(ApplicationTester $I, PageTree $pageTree): void
    {
        $I->useExistingSession('admin');
        $I->click('[data-modulemenu-identifier="web_info"]');
        $I->waitForElement('#typo3-pagetree-tree .nodes .node', 5);
        $pageTree->openPath(['styleguide TCA demo']);
        $I->switchToContentFrame();
    }

    private function infoMenuDataProvider(): array
    {
        return [
            ['option' => 'Pagetree Overview', 'expect' => 'Pagetree Overview'],
            ['option' => 'Localization Overview', 'expect' => 'Localization Overview'],
        ];
    }

    /**
     * @dataProvider infoMenuDataProvider
     */
    public function seeInfoSubModules(ApplicationTester $I, Example $exampleData): void
    {
        $I->amGoingTo('select ' . $exampleData['option'] . ' in dropdown');
        $I->waitForElementVisible('.t3-js-jumpMenuBox');
        $I->wait(1);
        $I->selectOption('.t3-js-jumpMenuBox', $exampleData['option']);
        $I->wait(1);
        $I->waitForText($exampleData['expect']);
        $I->see($exampleData['expect'], 'h1');
    }
}
