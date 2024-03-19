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

namespace TYPO3\CMS\Core\Tests\Acceptance\Application\Impexp;

use TYPO3\CMS\Core\Tests\Acceptance\Support\ApplicationTester;
use TYPO3\CMS\Core\Tests\Acceptance\Support\Helper\ModalDialog;
use TYPO3\CMS\Core\Tests\Acceptance\Support\Helper\PageTree;

/**
 * Various export related tests
 */
final class ExportCest extends AbstractCest
{
    private string $contextMenuExport = '[data-callback-action=exportT3d]';
    private string $contextMenuMore = 'li.context-menu-item-submenu';
    private string $inModuleHeader = '.module-docheader';
    private string $inModuleTabs = '#ImportExportController .nav-tabs';
    private string $inModuleTabsBody = '#ImportExportController .tab-content';
    private string $inTabConfiguration = '#export-configuration';
    private string $inFlashMessages = '.typo3-messages';

    public function _before(ApplicationTester $I): void
    {
        $I->useExistingSession('admin');
        $I->click('List');
        $I->waitForElement('#typo3-pagetree-tree .nodes .node');
    }

    public function exportPageAndRecordsDisplaysTitleOfSelectedPageInModuleHeader(ApplicationTester $I, PageTree $pageTree): void
    {
        $pageTree->openPath(['styleguide TCA demo']);

        $selectedPageTitle = 'elements t3editor';
        $selectedPageIcon = '//*[text()=\'' . $selectedPageTitle . '\']/../*[contains(@class, \'node-icon-container\')]';

        $I->click($selectedPageIcon);
        $this->selectInContextMenu($I, [$this->contextMenuMore, $this->contextMenuExport]);
        $I->switchToContentFrame();
        $I->waitForText($selectedPageTitle);
        $I->waitForElementNotVisible('#nprogress');
        $I->see($selectedPageTitle, $this->inModuleHeader);

        $I->click('Update', $this->inTabConfiguration);
        $this->timeoutForAjaxRequest($I);
        $I->see($selectedPageTitle, $this->inModuleHeader);
    }

    public function exportTableDisplaysTitleOfRootPageInModuleHeader(ApplicationTester $I, PageTree $pageTree): void
    {
        $rootPageTitle = 'New TYPO3 site';
        $tablePageTitle = 'elements t3editor';
        $tableTitle = 'Form engine elements - t3editor';
        $listModuleHeader = '.module-docheader';
        $listModuleBtnExport = 'a[title="Export"]';

        $pageTree->openPath(['styleguide TCA demo', $tablePageTitle]);
        $I->switchToContentFrame();
        // List module single table mode
        $I->waitForText($tableTitle);
        $I->waitForElementNotVisible('#nprogress');
        $I->click($tableTitle);

        $I->waitForElementVisible($listModuleHeader . ' ' . $listModuleBtnExport, 5);
        $I->click($listModuleBtnExport, $listModuleHeader);
        $I->waitForElementVisible($this->inTabConfiguration, 5);
        $I->see($rootPageTitle, $this->inModuleHeader);
        $I->dontSee($tablePageTitle, $this->inModuleHeader);

        $I->click('Update', $this->inTabConfiguration);
        $this->timeoutForAjaxRequest($I);
        $I->see($rootPageTitle, $this->inModuleHeader);
        $I->dontSee($tablePageTitle, $this->inModuleHeader);
    }

    public function exportRecordDisplaysTitleOfRootPageInModuleHeader(ApplicationTester $I, PageTree $pageTree): void
    {
        $rootPageTitle = 'New TYPO3 site';
        $recordPageTitle = 'elements t3editor';
        $recordTable = '#recordlist-tx_styleguide_elements_t3editor';
        $recordIcon = 'tr:first-child button[data-contextmenu-trigger]';

        $pageTree->openPath(['styleguide TCA demo', $recordPageTitle]);
        $I->switchToContentFrame();
        // List module single table mode
        $I->waitForText($recordPageTitle);
        $I->waitForElementNotVisible('#nprogress');
        $I->click($recordIcon, $recordTable);
        $this->selectInContextMenu($I, [$this->contextMenuMore, $this->contextMenuExport]);
        $I->waitForElementVisible($this->inTabConfiguration, 5);
        $I->see($rootPageTitle, $this->inModuleHeader);
        $I->dontSee($recordPageTitle, $this->inModuleHeader);

        $I->click('Update', $this->inTabConfiguration);
        $this->timeoutForAjaxRequest($I);
        $I->see($rootPageTitle, $this->inModuleHeader);
        $I->dontSee($recordPageTitle, $this->inModuleHeader);
    }

    public function saveAndDeletePresetSucceeds(ApplicationTester $I, ModalDialog $modalDialog, PageTree $pageTree): void
    {
        $pageTitle = 'staticdata';
        $exportPageTitle = 'Export pagetree configuration';
        $pageIcon = '//*[text()=\'' . $pageTitle . '\']/../*[contains(@class, \'node-icon-container\')]';
        $tabExport = 'a[href="#export-filepreset"]';
        $contentExport = '#export-filepreset';
        $presetTitle = 'My First Preset';
        $inputPresetTitle = 'input[name="tx_impexp[preset][title]"]';
        $buttonSavePreset = 'button[name="preset[save]"]';
        $buttonDeletePreset = 'button[name="preset[delete]"]';
        $selectPreset = 'select[name="preset[select]"]';

        $pageTree->openPath(['styleguide TCA demo']);

        $I->click($pageIcon);
        $this->selectInContextMenu($I, [$this->contextMenuMore, $this->contextMenuExport]);
        $I->switchToContentFrame();
        $I->waitForText($exportPageTitle);
        $I->waitForElementNotVisible('#nprogress');

        $I->click($tabExport, $this->inModuleTabs);
        $I->waitForElementVisible($contentExport, 5);
        $I->fillField($this->inModuleTabsBody . ' ' . $inputPresetTitle, $presetTitle);
        $I->click($buttonSavePreset, $this->inModuleTabsBody);

        // don't use $modalDialog->clickButtonInDialog due to too low timeout
        $modalDialog->canSeeDialog();
        $I->click('OK', ModalDialog::$openedModalButtonContainerSelector);
        $I->waitForElementNotVisible(ModalDialog::$openedModalSelector, 30);
        $this->timeoutForAjaxRequest($I);

        $I->switchToContentFrame();
        $I->canSeeElement($this->inFlashMessages . ' .alert.alert-info');
        $I->canSee(sprintf('New preset "%s" is created', $presetTitle), $this->inFlashMessages . ' .alert.alert-info .alert-message');

        $I->click($tabExport, $this->inModuleTabs);
        $I->waitForElementVisible($contentExport, 5);
        $I->selectOption($this->inModuleTabsBody . ' ' . $selectPreset, $presetTitle);
        $I->click($buttonDeletePreset, $this->inModuleTabsBody);

        // don't use $modalDialog->clickButtonInDialog due to too low timeout
        $modalDialog->canSeeDialog();
        $I->click('OK', ModalDialog::$openedModalButtonContainerSelector);
        $I->waitForElementNotVisible(ModalDialog::$openedModalSelector, 30);
        $this->timeoutForAjaxRequest($I);

        $I->switchToContentFrame();
        $I->canSeeElement($this->inFlashMessages . ' .alert.alert-info');
        $flashMessage = $I->grabTextFrom($this->inFlashMessages . ' .alert.alert-info .alert-message');
        $I->assertMatchesRegularExpression('/Preset #[0-9]+ deleted!/', $flashMessage);
    }

    public function exportPageAndRecordsFromPageTree(ApplicationTester $I, PageTree $pageTree): void
    {
        $pageTitle = 'staticdata';
        $exportPageTitle = 'Export pagetree configuration';
        $pageIcon = '//*[text()=\'' . $pageTitle . '\']/../*[contains(@class, \'node-icon-container\')]';
        $tabExport = 'a[href="#export-filepreset"]';
        $contentExport = '#export-filepreset';
        $buttonSaveToFile = 'tx_impexp[save_export]';

        $pageTree->openPath(['styleguide TCA demo']);

        $I->click($pageIcon);
        $this->selectInContextMenu($I, [$this->contextMenuMore, $this->contextMenuExport]);
        $I->switchToContentFrame();
        $I->waitForText($exportPageTitle);
        $I->waitForElementNotVisible('#nprogress');

        $I->cantSee('No tree exported - only tables on the page.', $this->inModuleTabsBody);
        $I->see('Inside pagetree');
        $I->dontSee('Outside pagetree');
        $I->click($tabExport, $this->inModuleTabs);
        $I->waitForElementVisible($contentExport, 5);
        $I->click($buttonSaveToFile, $this->inModuleTabsBody);
        $I->wait(1);
        $I->canSeeElement($this->inFlashMessages . ' .alert.alert-success');
        $I->canSee('SAVED FILE', $this->inFlashMessages . ' .alert.alert-success .alert-title');
        $flashMessage = $I->grabTextFrom($this->inFlashMessages . ' .alert.alert-success .alert-message');
        $I->assertMatchesRegularExpression('/Saved in ["][^"]+["], bytes/', $flashMessage);
    }

    public function exportTable(ApplicationTester $I): void
    {
        $rootPage = '#identifier-0_0 .node-name';
        $rootPageTitle = 'New TYPO3 site';
        $beUsergroupTableTitle = 'Backend usergroup';
        $listModuleHeader = '.module-docheader';
        $listModuleBtnExport = 'a[title="Export"]';
        $tabExport = 'a[href="#export-filepreset"]';
        $contentExport = '#export-filepreset';
        $buttonSaveToFile = 'tx_impexp[save_export]';

        $I->canSeeElement($rootPage);
        $I->click($rootPage);
        $I->switchToContentFrame();
        $I->waitForText($rootPageTitle);
        $I->waitForElementNotVisible('#nprogress');

        $I->click($beUsergroupTableTitle);
        $I->waitForElementVisible($listModuleHeader . ' ' . $listModuleBtnExport, 5);
        $I->click($listModuleBtnExport, $listModuleHeader);

        $I->waitForElementVisible($tabExport, 5);
        $I->canSee('No tree exported - only tables on the page.', $this->inModuleTabsBody);
        $I->canSee('Export tables from pages', $this->inModuleTabsBody);
        $I->dontSee('Inside pagetree');
        $I->see('Outside pagetree');
        $I->click($tabExport, $this->inModuleTabs);
        $I->waitForElementVisible($contentExport, 5);
        $I->click($buttonSaveToFile, $this->inModuleTabsBody);
        $I->wait(1);
        $I->canSeeElement($this->inFlashMessages . ' .alert.alert-success');
        $I->canSee('SAVED FILE', $this->inFlashMessages . ' .alert.alert-success .alert-title');
        $flashMessage = $I->grabTextFrom($this->inFlashMessages . ' .alert.alert-success .alert-message');
        $I->assertMatchesRegularExpression('/Saved in ["][^"]+["], bytes/', $flashMessage);
    }

    public function exportRecord(ApplicationTester $I): void
    {
        $rootPage = '#identifier-0_0 .node-name';
        $rootPageTitle = 'New TYPO3 site';
        $sysLanguageTable = '#recordlist-be_groups';
        $sysLanguageIcon = 'tr:first-child button[data-contextmenu-trigger]';
        $tabExport = 'a[href="#export-filepreset"]';
        $contentExport = '#export-filepreset';
        $buttonSaveToFile = 'tx_impexp[save_export]';

        // select root page in list module
        $I->canSeeElement($rootPage);
        $I->click($rootPage);
        $I->switchToContentFrame();
        $I->waitForElementNotVisible('#nprogress');
        $I->waitForText($rootPageTitle);
        $I->click($sysLanguageIcon, $sysLanguageTable);
        $this->selectInContextMenu($I, [$this->contextMenuMore, $this->contextMenuExport]);

        $I->waitForElementVisible($tabExport, 5);
        $I->canSee('No tree exported - only tables on the page.', $this->inModuleTabsBody);
        $I->canSee('Export single record', $this->inModuleTabsBody);
        $I->dontSee('Inside pagetree');
        $I->see('Outside pagetree');
        $I->click($tabExport, $this->inModuleTabs);
        $I->waitForElementVisible($contentExport, 5);
        $I->click($buttonSaveToFile, $this->inModuleTabsBody);
        $I->wait(1);
        $I->canSeeElement($this->inFlashMessages . ' .alert.alert-success');
        $I->canSee('SAVED FILE', $this->inFlashMessages . ' .alert.alert-success .alert-title');
        $flashMessage = $I->grabTextFrom($this->inFlashMessages . ' .alert.alert-success .alert-message');
        $I->assertMatchesRegularExpression('/Saved in ["][^"]+["], bytes/', $flashMessage);
    }
}
