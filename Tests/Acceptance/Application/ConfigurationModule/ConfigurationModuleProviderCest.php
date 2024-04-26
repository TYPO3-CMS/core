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

namespace TYPO3\CMS\Core\Tests\Acceptance\Application\ConfigurationModule;

use TYPO3\CMS\Core\Tests\Acceptance\Support\ApplicationTester;

/**
 * Configuration module provider tests
 */
final class ConfigurationModuleProviderCest
{
    public function _before(ApplicationTester $I): void
    {
        $I->useExistingSession('admin');
        $I->scrollTo('[data-modulemenu-identifier="system_config"]');
        $I->see('Configuration', '[data-modulemenu-identifier="system_config"]');
        $I->click('[data-modulemenu-identifier="system_config"]');
        $I->switchToContentFrame();
    }

    public function selectAndDisplayConfiguration(ApplicationTester $I): void
    {
        // Module can be accessed
        // Sorting is applied and TYPO3_CONF_VARS is the default provider to display
        $I->see('Configuration of "$GLOBALS[\'TYPO3_CONF_VARS\'] (Global Configuration)"', 'h1');

        // Middlewares provider exists
        $I->selectOption('select[name=tree]', 'HTTP Middlewares (PSR-15)');

        // Middleware provider can be loaded
        $I->waitForElementVisible('#ConfigurationView');
        $I->see('Configuration of "HTTP Middlewares (PSR-15)"', 'h1');

        // Tree search can be applied
        $I->fillField('#searchValue', 'authentication');
        $I->waitForText('typo3/cms-frontend/authentication');
        $I->see('typo3/cms-frontend/authentication');
    }

    public function seeAllPagesInDropDown(ApplicationTester $I): void
    {
        $itemList = [
            '$GLOBALS[\'TYPO3_CONF_VARS\'] (Global Configuration)',
            '$GLOBALS[\'TCA\'] (Table configuration array)',
            '$GLOBALS[\'T3_SERVICES\'] (Registered Services)',
            '$GLOBALS[\'TBE_STYLES\'] (Skinning Styles)',
            '$GLOBALS[\'TYPO3_USER_SETTINGS\'] (User Settings Configuration)',
            'Table permissions per page type',
            '$GLOBALS[\'BE_USER\']->uc (User Settings)',
            '$GLOBALS[\'BE_USER\']->getTSConfig() (User TSconfig)',
            'Backend Routes',
            'Backend Modules',
            'HTTP Middlewares (PSR-15)',
            'Sites: TCA configuration',
            'Sites: YAML configuration',
            'Event Listeners (PSR-14)',
            'MFA providers',
        ];
        foreach ($itemList as $item) {
            $I->selectOption('select[name=tree]', $item);
            $I->see('Configuration of "' . $item . '"', 'h1');
        }
    }
}
