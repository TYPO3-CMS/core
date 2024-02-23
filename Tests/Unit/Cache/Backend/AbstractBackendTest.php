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

namespace TYPO3\CMS\Core\Tests\Unit\Cache\Backend;

use PHPUnit\Framework\Attributes\Test;
use TYPO3\CMS\Core\Tests\Unit\Cache\Backend\Fixtures\ConcreteBackendFixture;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

final class AbstractBackendTest extends UnitTestCase
{
    protected bool $resetSingletonInstances = true;

    #[Test]
    public function theConstructorCallsSetterMethodsForAllSpecifiedOptions(): void
    {
        // The fixture class implements methods setSomeOption() and getSomeOption()
        $backend = new ConcreteBackendFixture('Testing', ['someOption' => 'someValue']);
        self::assertSame('someValue', $backend->getSomeOption());
    }
}
