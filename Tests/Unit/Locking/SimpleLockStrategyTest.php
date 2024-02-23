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

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Locking\SemaphoreLockStrategy;
use TYPO3\CMS\Core\Locking\SimpleLockStrategy;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

final class SimpleLockStrategyTest extends UnitTestCase
{
    #[Test]
    public function constructorCreatesLockDirectoryIfNotExisting(): void
    {
        GeneralUtility::rmdir(Environment::getVarPath() . '/' . SimpleLockStrategy::FILE_LOCK_FOLDER, true);
        new SimpleLockStrategy('999999999');
        self::assertDirectoryExists(Environment::getVarPath() . '/' . SimpleLockStrategy::FILE_LOCK_FOLDER);
    }

    #[Test]
    public function constructorSetsResourceToPathWithIdIfUsingSimpleLocking(): void
    {
        $lock = $this->getAccessibleMock(SimpleLockStrategy::class, null, ['999999999']);
        self::assertSame(Environment::getVarPath() . '/' . SimpleLockStrategy::FILE_LOCK_FOLDER . 'simple_' . md5('999999999'), $lock->_get('filePath'));
    }

    #[Test]
    public function acquireFixesPermissionsOnLockFile(): void
    {
        if (Environment::isWindows()) {
            self::markTestSkipped('Test not available on Windows.');
        }
        // Use a very high id to be unique
        $lock = $this->getAccessibleMock(SimpleLockStrategy::class, null, ['999999999']);

        $pathOfLockFile = $lock->_get('filePath');

        $GLOBALS['TYPO3_CONF_VARS']['SYS']['fileCreateMask'] = '0777';

        // Acquire lock, get actual file permissions and clean up
        $lock->acquire();
        clearstatcache();
        $resultFilePermissions = substr(decoct(fileperms($pathOfLockFile)), 2);
        $lock->release();
        self::assertEquals('0777', $resultFilePermissions);
    }

    #[Test]
    public function releaseRemovesLockfileInTypo3TempLocks(): void
    {
        $lock = $this->getAccessibleMock(SimpleLockStrategy::class, null, ['999999999']);

        $pathOfLockFile = $lock->_get('filePath');

        $lock->acquire();
        $lock->release();

        self::assertFalse(is_file($pathOfLockFile));
    }

    public static function releaseDoesNotRemoveFilesNotWithinTypo3TempLocksDirectoryDataProvider(): array
    {
        return [
            'not within project path' => [tempnam(sys_get_temp_dir(), 'foo')],
            'directory traversal' => [Environment::getVarPath() . '/../var/lock/foo'],
            'directory traversal 2' => [Environment::getVarPath() . '/lock/../../var/lock/foo'],
        ];
    }

    #[DataProvider('releaseDoesNotRemoveFilesNotWithinTypo3TempLocksDirectoryDataProvider')]
    #[Test]
    public function releaseDoesNotRemoveFilesNotWithinTypo3TempLocksDirectory(string $file): void
    {
        // Create test file
        @touch($file);
        // Create instance, set lock file to invalid path
        $lock = $this->getAccessibleMock(SimpleLockStrategy::class, null, ['999999999']);
        $lock->_set('filePath', $file);
        $lock->_set('isAcquired', true);

        // Call release method
        $lock->release();
        // Check if file is still there and clean up
        $fileExists = is_file($file);
        @unlink($file);
        self::assertTrue($fileExists);
    }

    #[Test]
    public function getPriorityReturnsDefaultPriority(): void
    {
        self::assertEquals(SemaphoreLockStrategy::DEFAULT_PRIORITY, SemaphoreLockStrategy::getPriority());
    }

    #[Test]
    public function setPriority(): void
    {
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['locking']['strategies'][SimpleLockStrategy::class]['priority'] = 10;

        self::assertEquals(10, SimpleLockStrategy::getPriority());
        unset($GLOBALS['TYPO3_CONF_VARS']['SYS']['locking']['strategies'][SimpleLockStrategy::class]['priority']);
    }
}
