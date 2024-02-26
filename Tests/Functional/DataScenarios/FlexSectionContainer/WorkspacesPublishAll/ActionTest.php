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

namespace TYPO3\CMS\Core\Tests\Functional\DataScenarios\FlexSectionContainer\WorkspacesPublishAll;

use PHPUnit\Framework\Attributes\Test;
use TYPO3\CMS\Core\Tests\Functional\DataScenarios\FlexSectionContainer\AbstractActionWorkspacesTestCase;

final class ActionTest extends AbstractActionWorkspacesTestCase
{
    #[Test]
    public function verifyCleanReferenceIndex(): void
    {
        // The test verifies the imported data set has a clean reference index by the check in tearDown()
        self::assertTrue(true);
    }

    #[Test]
    public function deleteSection(): void
    {
        parent::deleteSection();
        $this->actionService->publishWorkspace(self::VALUE_WorkspaceId);
        $this->assertCSVDataSet(__DIR__ . '/DataSet/deleteSection.csv');
    }

    #[Test]
    public function changeSorting(): void
    {
        parent::changeSorting();
        $this->actionService->publishWorkspace(self::VALUE_WorkspaceId);
        $this->assertCSVDataSet(__DIR__ . '/DataSet/changeSorting.csv');
    }
}
