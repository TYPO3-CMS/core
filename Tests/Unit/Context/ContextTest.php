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

namespace TYPO3\CMS\Core\Tests\Unit\Context;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Context\Exception\AspectNotFoundException;
use TYPO3\CMS\Core\Context\UserAspect;
use TYPO3\CMS\Core\Context\WorkspaceAspect;
use TYPO3\CMS\Core\Registry;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

final class ContextTest extends UnitTestCase
{
    /**
     * Date provider for hasAspectReturnsTrueOnExistingAspect
     */
    public static function validAspectKeysDataProvider(): array
    {
        return [
            ['myfirst'],
            ['mysecond'],
            ['date'],
            ['visibility'],
            ['backend.user'],
            ['frontend.user'],
            ['workspace'],
        ];
    }

    #[DataProvider('validAspectKeysDataProvider')]
    #[Test]
    public function hasAspectReturnsTrueOnExistingAspect(string $aspectName): void
    {
        $subject = new Context([
            'myfirst' => new UserAspect(),
            'mysecond' => new UserAspect(),
        ]);
        self::assertTrue($subject->hasAspect($aspectName));
    }

    /**
     * Date provider for hasAspectReturnsFalseOnNonExistingAspect
     */
    public static function invalidAspectKeysDataProvider(): array
    {
        return [
            ['visible'],
            ['frontenduser'],
            ['compatibility'],
        ];
    }

    #[DataProvider('invalidAspectKeysDataProvider')]
    #[Test]
    public function hasAspectReturnsFalseOnNonExistingAspect(string $aspectName): void
    {
        $subject = new Context([
            'myfirst' => new UserAspect(),
            'mysecond' => new UserAspect(),
        ]);
        self::assertFalse($subject->hasAspect($aspectName));
    }

    #[Test]
    public function constructorAddsValidAspect(): void
    {
        $subject = new Context([
            'coolio' => new UserAspect(),
            'uncoolio' => new Registry(),
        ]);
        self::assertTrue($subject->hasAspect('coolio'));
        self::assertFalse($subject->hasAspect('uncoolio'));
    }

    #[Test]
    public function getAspectThrowsExceptionOnInvalidAspect(): void
    {
        $aspect = new UserAspect();
        $subject = new Context([
            'coolio' => $aspect,
        ]);

        $this->expectException(AspectNotFoundException::class);
        $this->expectExceptionCode(1527777641);
        $subject->getAspect('uncoolio');
    }

    #[Test]
    public function getAspectReturnsValidAspect(): void
    {
        $aspect = new UserAspect();
        $subject = new Context([
            'coolio' => $aspect,
        ]);

        self::assertSame($aspect, $subject->getAspect('coolio'));
    }

    #[Test]
    public function invalidAspectFromgetPropertyFromAspectThrowsException(): void
    {
        $aspect = new UserAspect();
        $subject = new Context([
            'coolio' => $aspect,
        ]);

        $this->expectException(AspectNotFoundException::class);
        $this->expectExceptionCode(1527777868);
        $subject->getPropertyFromAspect('uncoolio', 'does not matter');
    }

    #[Test]
    public function invalidPropertyFromgetPropertyFromAspectReturnsDefault(): void
    {
        $defaultValue = 'default value';
        $aspect = new UserAspect();
        $subject = new Context([
            'coolio' => $aspect,
        ]);

        $result = $subject->getPropertyFromAspect('coolio', 'unknownproperty', $defaultValue);
        self::assertEquals($defaultValue, $result);
    }

    #[Test]
    public function validPropertyFromgetPropertyFromAspectReturnsValue(): void
    {
        $aspect = new WorkspaceAspect(13);
        $subject = new Context([
            'coolio' => $aspect,
        ]);

        $result = $subject->getPropertyFromAspect('coolio', 'id');
        self::assertEquals(13, $result);
    }

    #[Test]
    public function setAspectSetsAnAspectAndCanReturnIt(): void
    {
        $aspect = new UserAspect();
        $subject = new Context();

        $subject->setAspect('coolio', $aspect);
        self::assertSame($aspect, $subject->getAspect('coolio'));
    }

    #[Test]
    public function setAspectOverridesAnExisting(): void
    {
        $initialAspect = new UserAspect();
        $aspectOverride = new UserAspect();
        $subject = new Context([
            'coolio' => $initialAspect,
        ]);

        $subject->setAspect('coolio', $aspectOverride);
        self::assertNotSame($initialAspect, $subject->getAspect('coolio'));
        self::assertSame($aspectOverride, $subject->getAspect('coolio'));
    }
}
