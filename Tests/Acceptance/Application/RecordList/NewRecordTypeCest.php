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

namespace TYPO3\CMS\Core\Tests\Acceptance\Application\RecordList;

use TYPO3\CMS\Core\Tests\Acceptance\Support\ApplicationTester;
use TYPO3\CMS\Core\Tests\Acceptance\Support\Helper\ModalDialog;
use TYPO3\CMS\Core\Tests\Acceptance\Support\Helper\PageTree;

/**
 * Cases concerning the "new record" module
 */
final class NewRecordTypeCest
{
    public function _before(ApplicationTester $I): void
    {
        $I->useExistingSession('admin');
    }

    public function opensPageWizardModalOnNewPageButtonClick(ApplicationTester $I, PageTree $pageTree, ModalDialog $modalDialog): void
    {
        $I->amGoingTo('create a record');
        $I->click('Records');
        $I->waitForElementNotVisible('typo3-backend-progress-bar');
        $pageTree->openPath(['styleguide TCA demo']);
        $I->wait(0.2);
        $I->switchToContentFrame();

        $I->click('.module-docheader .btn[title="Create new record"]');
        $I->wait(0.2);
        $I->canSee('New record');

        $I->waitForElementVisible('typo3-backend-new-page-wizard-button[data-page-create="inside"]');
        $I->click('typo3-backend-new-page-wizard-button[data-page-create="inside"]');

        $modalDialog->canSeeDialog();
        $I->waitForElementVisible('typo3-backend-page-wizard');
    }
}
