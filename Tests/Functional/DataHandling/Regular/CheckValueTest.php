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

namespace TYPO3\CMS\Core\Tests\Functional\DataHandling\Regular;

use PHPUnit\Framework\Attributes\Test;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Tests\Functional\DataHandling\AbstractDataHandlerActionTestCase;

/**
 * Tests related to DataHandler::checkValue()
 */
final class CheckValueTest extends AbstractDataHandlerActionTestCase
{
    protected array $testExtensionsToLoad = [
        'typo3/sysext/core/Tests/Functional/Fixtures/Extensions/test_datahandler',
    ];

    protected function setUp(): void
    {
        parent::setUp();
        $this->importCSVDataSet(__DIR__ . '/DataSet/ImportDefault.csv');
    }

    #[Test]
    public function radioButtonValueMustBeDefinedInTcaItems(): void
    {
        $record = $this->insertRecordWithRadioFieldValue('predefined value');

        self::assertEquals('predefined value', $record['tx_testdatahandler_radio']);
    }

    #[Test]
    public function radioButtonValueMustComeFromItemsProcFuncIfNotDefinedInTcaItems(): void
    {
        $record = $this->insertRecordWithRadioFieldValue('processed value');

        self::assertEquals('processed value', $record['tx_testdatahandler_radio']);
    }

    #[Test]
    public function radioButtonValueIsNotSavedIfNotDefinedInTcaItemsOrProcessingItems(): void
    {
        $record = $this->insertRecordWithRadioFieldValue('some other value');

        self::assertEquals('', $record['tx_testdatahandler_radio']);
    }

    protected function insertRecordWithRadioFieldValue($value): array
    {
        // pid 88 comes from ImportDefault
        $result = $this->actionService->createNewRecord('tt_content', 88, [
            'tx_testdatahandler_radio' => $value,
        ]);
        $recordUid = $result['tt_content'][0];

        $record = BackendUtility::getRecord('tt_content', $recordUid);

        return $record;
    }
}
