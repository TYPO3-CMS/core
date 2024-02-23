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

namespace TYPO3\CMS\Core\Tests\Unit\Routing\Aspect;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Routing\Aspect\AspectFactory;
use TYPO3\CMS\Core\Routing\Aspect\AspectInterface;
use TYPO3\CMS\Core\Routing\Aspect\PersistedMappableAspectInterface;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

final class AspectFactoryTest extends UnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['routing']['aspects'] = [
            'Persisted' => get_class($this->createMock(PersistedMappableAspectInterface::class)),
            'Aspect' => get_class($this->createMock(AspectInterface::class)),
        ];
    }

    #[Test]
    public function createAspectsThrowsExceptionOnMissingType(): void
    {
        $contextMock = $this->createMock(Context::class);
        $aspectFactory = new AspectFactory($contextMock);
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionCode(1538079481);
        $aspectFactory->createAspects(
            ['a' => []],
            $this->createMock(SiteLanguage::class),
            $this->createMock(Site::class)
        );
    }

    #[Test]
    public function createAspectsThrowsExceptionOnUnregisteredType(): void
    {
        $contextMock = $this->createMock(Context::class);
        $aspectFactory = new AspectFactory($contextMock);
        $this->expectException(\OutOfRangeException::class);
        $this->expectExceptionCode(1538079482);
        $aspectFactory->createAspects(
            ['a' => ['type' => 'Undefined']],
            $this->createMock(SiteLanguage::class),
            $this->createMock(Site::class)
        );
    }

    public static function aspectsDataProvider(): array
    {
        return [
            'single aspect' => [
                [
                    'a' => ['type' => 'Aspect'],
                ],
                [
                    'a' => AspectInterface::class,
                ],
            ],
            'both non-persisted' => [
                [
                    'a' => ['type' => 'Aspect'],
                    'b' => ['type' => 'Aspect'],
                ],
                [
                    'a' => AspectInterface::class,
                    'b' => AspectInterface::class,
                ],
            ],
            'both persisted' => [
                [
                    'a' => ['type' => 'Persisted'],
                    'b' => ['type' => 'Persisted'],
                ],
                [
                    'a' => PersistedMappableAspectInterface::class,
                    'b' => PersistedMappableAspectInterface::class,
                ],
            ],
            // persisted shall be sorted to the end
            'first persisted, second non-persisted' => [
                [
                    'a' => ['type' => 'Persisted'],
                    'b' => ['type' => 'Aspect'],
                ],
                [
                    'b' => AspectInterface::class,
                    'a' => PersistedMappableAspectInterface::class,
                ],
            ],
            // persisted shall be sorted to the end
            'many persisted, many non-persisted' => [
                [
                    'a' => ['type' => 'Persisted'],
                    'b' => ['type' => 'Aspect'],
                    'c' => ['type' => 'Persisted'],
                    'd' => ['type' => 'Aspect'],
                ],
                [
                    'b' => AspectInterface::class,
                    'd' => AspectInterface::class,
                    'a' => PersistedMappableAspectInterface::class,
                    'c' => PersistedMappableAspectInterface::class,
                ],
            ],
        ];
    }

    /**
     * @param string[] $expectation
     */
    #[DataProvider('aspectsDataProvider')]
    #[Test]
    public function aspectsAreCreatedAndSorted(array $settings, array $expectation): void
    {
        $contextMock = $this->createMock(Context::class);
        $aspectFactory = new AspectFactory($contextMock);
        $aspects = $aspectFactory->createAspects(
            $settings,
            $this->createMock(SiteLanguage::class),
            $this->createMock(Site::class)
        );
        self::assertSame(array_keys($aspects), array_keys($expectation));
        array_walk($aspects, static function ($aspect, $key) use ($expectation) {
            static::assertInstanceOf($expectation[$key], $aspect);
        });
    }
}
