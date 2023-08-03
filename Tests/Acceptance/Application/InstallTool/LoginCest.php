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

namespace TYPO3\CMS\Core\Tests\Acceptance\Application\InstallTool;

use TYPO3\CMS\Core\Tests\Acceptance\Support\ApplicationTester;

final class LoginCest extends AbstractCest
{
    public function installToolLogin(ApplicationTester $I): void
    {
        $I->amGoingTo('assert the install tool is locked in the first place');
        $I->waitForText('The Install Tool is locked', 10, '.callout-warning');
        $I->assertFileDoesNotExist($this->getEnableInstallToolFilePath());

        $I->amGoingTo('lock the tool without logging in');
        $I->writeToFile($this->getEnableInstallToolFilePath(), '');
        $I->seeFileFound($this->getEnableInstallToolFilePath());
        $I->reloadPage();
        $I->waitForElementVisible('#t3-install-form-password');
        $I->see('Login to TYPO3 Install Tool');
        $I->click('Lock Install Tool again');
        $I->see('The Install Tool is locked');
        $I->dontSeeFileFound($this->getEnableInstallToolFilePath());

        $I->amGoingTo('log into Install Tool');
        $this->logIntoInstallTool($I);

        $I->amGoingTo('assert page Maintenance contains the 8 expected cards');
        $I->click('Maintenance');
        $I->see('Maintenance', 'h1');
        $I->see('Flush TYPO3 and PHP Cache', 'h2.card-title');
        $I->see('Analyze Database Structure', 'h2.card-title');
        $I->see('Remove Temporary Assets', 'h2.card-title');
        $I->see('Rebuild PHP Autoload Information', 'h2.card-title');
        $I->see('Clear Persistent Database Tables', 'h2.card-title');
        $I->see('Create Administrative User', 'h2.card-title');
        $I->see('Reset Backend User Preferences', 'h2.card-title');
        $I->see('Manage Language Packs', 'h2.card-title');
        $I->seeNumberOfElements('.card', 8);

        $I->amGoingTo('assert page Settings contains the 6 expected cards');
        $I->click('Settings');
        $I->see('Settings', 'h1');
        $I->see('Extension Configuration', 'h2.card-title');
        $I->see('Change Install Tool Password', 'h2.card-title');
        $I->see('Manage System Maintainers', 'h2.card-title');
        $I->see('Configuration Presets', 'h2.card-title');
        $I->see('Feature Toggles', 'h2.card-title');
        $I->see('Configure Installation-Wide Options', 'h2.card-title');
        $I->seeNumberOfElements('.card', 6);

        $I->amGoingTo('assert page Upgrade contains the 7 expected cards');
        $I->click('Upgrade');
        $I->see('Upgrade', 'h1');
        $I->see('Update TYPO3 Core', 'h2.card-title');
        $I->see('Upgrade Wizard', 'h2.card-title');
        $I->see('View Upgrade Documentation', 'h2.card-title');
        $I->see('Check TCA in ext_tables.php', 'h2.card-title');
        $I->see('Check for Broken Extensions', 'h2.card-title');
        $I->see('Check TCA Migrations', 'h2.card-title');
        $I->see('Scan Extension Files', 'h2.card-title');
        $I->seeNumberOfElements('.card', 7);

        $I->amGoingTo('assert page Environment contains the 6 expected cards');
        $I->click('Environment');
        $I->see('Environment', 'h1');
        $I->see('Environment Overview', 'h2.card-title');
        $I->see('Environment Status', 'h2.card-title');
        $I->see('Directory Status', 'h2.card-title');
        $I->see('PHP Info', 'h2.card-title');
        $I->see('Test Mail Setup', 'h2.card-title');
        $I->see('Image Processing', 'h2.card-title');
        $I->seeNumberOfElements('.card', 6);
    }
}
