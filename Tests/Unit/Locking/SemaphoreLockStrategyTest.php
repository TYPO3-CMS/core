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

namespace TYPO3\CMS\Core\Tests\Unit\Locking;

use PHPUnit\Framework\Attributes\Test;
use TYPO3\CMS\Core\Locking\SemaphoreLockStrategy;
use TYPO3\CMS\Core\Locking\SimpleLockStrategy;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * @requires function sem_get
 */
final class SemaphoreLockStrategyTest extends UnitTestCase
{
    #[Test]
    public function acquireGetsSemaphore(): void
    {
        $lock = new SemaphoreLockStrategy('99999');
        self::assertTrue($lock->acquire());
        $lock->release();
        $lock->destroy();
    }

    #[Test]
    public function getPriorityReturnsDefaultPriority(): void
    {
        self::assertEquals(SimpleLockStrategy::DEFAULT_PRIORITY, SimpleLockStrategy::getPriority());
    }

    #[Test]
    public function setPriority(): void
    {
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['locking']['strategies'][SemaphoreLockStrategy::class]['priority'] = 10;

        self::assertEquals(10, SemaphoreLockStrategy::getPriority());
        unset($GLOBALS['TYPO3_CONF_VARS']['SYS']['locking']['strategies'][SemaphoreLockStrategy::class]['priority']);
    }
}
