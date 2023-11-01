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

namespace TYPO3\CMS\Core\Tests\Acceptance\Application\Topbar;

use TYPO3\CMS\Core\Tests\Acceptance\Support\ApplicationTester;
use TYPO3\TestingFramework\Core\Acceptance\Helper\Topbar;

/**
 * Tests for the help module in the topbar
 */
final class HelpCest
{
    /**
     * Selector for the module container in the topbar
     */
    private static string $topBarModuleSelector = '#typo3-cms-backend-backend-toolbaritems-helptoolbaritem';

    public function _before(ApplicationTester $I): void
    {
        $I->useExistingSession('admin');
    }

    public function canSeeModuleInTopbar(ApplicationTester $I): void
    {
        $I->canSeeElement(self::$topBarModuleSelector);
    }

    public function seeAboutInHelpModule(ApplicationTester $I): void
    {
        $I->click(Topbar::$dropdownToggleSelector, self::$topBarModuleSelector);
        $I->canSee('About TYPO3 CMS', self::$topBarModuleSelector);
        $I->click('About TYPO3 CMS', self::$topBarModuleSelector);
        $I->switchToContentFrame();
        $I->see('Web Content Management System', 'h1');
    }

    public function seeOnlineDocumentationInHelpModule(ApplicationTester $I): void
    {
        $I->click(Topbar::$dropdownToggleSelector, self::$topBarModuleSelector);
        $I->canSee('TYPO3 Online Documentation', self::$topBarModuleSelector);
        $I->seeElement(self::$topBarModuleSelector . ' a[href="https://docs.typo3.org/"]');
    }
}
