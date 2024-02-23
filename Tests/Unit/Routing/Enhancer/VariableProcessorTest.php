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

namespace TYPO3\CMS\Core\Tests\Unit\Routing\Enhancer;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\CMS\Core\Routing\Enhancer\VariableProcessor;
use TYPO3\CMS\Core\Routing\Enhancer\VariableProcessorCache;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

final class VariableProcessorTest extends UnitTestCase
{
    public static function routePathDataProvider(): array
    {
        $plainInflatedRoutePath = '/static/{aa}/{bb}/{some_cc}/tail';
        $enforcedInflatedRoutePath = '/static/{!aa}/{bb}/{some_cc}/tail';

        return [
            'no arguments, no namespace (plain)' => [
                null,
                [],
                $plainInflatedRoutePath,
                '/static/{aa}/{bb}/{some_cc}/tail',
            ],
            'no arguments, no namespace (enforced)' => [
                null,
                [],
                $enforcedInflatedRoutePath,
                '/static/{!aa}/{bb}/{some_cc}/tail',
            ],
            'aa -> 1, no namespace (plain)' => [
                null,
                ['aa' => 1],
                $plainInflatedRoutePath,
                '/static/{1}/{bb}/{some_cc}/tail',
            ],
            'aa -> zz, no namespace (plain)' => [
                null,
                ['aa' => 'zz'],
                $plainInflatedRoutePath,
                '/static/{zz}/{bb}/{some_cc}/tail',
            ],
            'aa -> zz, no namespace (enforced)' => [
                null,
                ['aa' => 'zz'],
                $enforcedInflatedRoutePath,
                '/static/{!zz}/{bb}/{some_cc}/tail',
            ],
            'aa -> @any/nested, no namespace (plain)' => [
                null,
                ['aa' => '@any/nested'],
                $plainInflatedRoutePath,
                '/static/{qbeced67e6b340abc67a397f6e90bb0}/{bb}/{some_cc}/tail',
            ],
            'aa -> @any/nested, no namespace (enforced)' => [
                null,
                ['aa' => '@any/nested'],
                $enforcedInflatedRoutePath,
                '/static/{!qbeced67e6b340abc67a397f6e90bb0}/{bb}/{some_cc}/tail',
            ],
            'no arguments, first (plain)' => [
                'first',
                [],
                $plainInflatedRoutePath,
                '/static/{first__aa}/{first__bb}/{first__some_cc}/tail',
            ],
            'no arguments, first (enforced)' => [
                'first',
                [],
                $enforcedInflatedRoutePath,
                '/static/{!first__aa}/{first__bb}/{first__some_cc}/tail',
            ],
            'aa -> zz, first (plain)' => [
                'first',
                ['aa' => 'zz'],
                $plainInflatedRoutePath,
                '/static/{first__zz}/{first__bb}/{first__some_cc}/tail',
            ],
            'aa -> zz, first (enforced)' => [
                'first',
                ['aa' => 'zz'],
                $enforcedInflatedRoutePath,
                '/static/{!first__zz}/{first__bb}/{first__some_cc}/tail',
            ],
            'aa -> any/nested, first (plain)' => [
                'first',
                ['aa' => 'any/nested'],
                $plainInflatedRoutePath,
                '/static/{first__any__nested}/{first__bb}/{first__some_cc}/tail',
            ],
            'aa -> any/nested, first (enforced)' => [
                'first',
                ['aa' => 'any/nested'],
                $enforcedInflatedRoutePath,
                '/static/{!first__any__nested}/{first__bb}/{first__some_cc}/tail',
            ],
            'aa -> @any/nested, first (plain)' => [
                'first',
                ['aa' => '@any/nested'],
                $plainInflatedRoutePath,
                '/static/{ab0ce8f9f822228b4f324ec38b9c038}/{first__bb}/{first__some_cc}/tail',
            ],
            'aa -> @any/nested, first (enforced)' => [
                'first',
                ['aa' => '@any/nested'],
                $enforcedInflatedRoutePath,
                '/static/{!ab0ce8f9f822228b4f324ec38b9c038}/{first__bb}/{first__some_cc}/tail',
            ],
        ];
    }

    #[DataProvider('routePathDataProvider')]
    #[Test]
    public function isRoutePathProcessed(?string $namespace, array $arguments, string $inflatedRoutePath, string $deflatedRoutePath): void
    {
        $subject = new VariableProcessor(new VariableProcessorCache());
        self::assertSame(
            $deflatedRoutePath,
            $subject->deflateRoutePath($inflatedRoutePath, $namespace, $arguments)
        );
        self::assertSame(
            $inflatedRoutePath,
            $subject->inflateRoutePath($deflatedRoutePath, $namespace, $arguments)
        );
    }

    public static function parametersDataProvider(): array
    {
        return [
            'no namespace, no arguments' => [
                [],
                ['a' => 'a', 'first__aa' => 'aa', 'first__second__aaa' => 'aaa', 'a9d66412d169b85537e11d9e49b75f9' => '@any'],
            ],
            'no namespace, a -> newA' => [
                ['a' => 'newA'],
                ['newA' => 'a', 'first__aa' => 'aa', 'first__second__aaa' => 'aaa', 'a9d66412d169b85537e11d9e49b75f9' => '@any'],
            ],
            'no namespace, a -> 1' => [
                ['a' => 1],
                [1 => 'a', 'first__aa' => 'aa', 'first__second__aaa' => 'aaa', 'a9d66412d169b85537e11d9e49b75f9' => '@any'],
            ],
            'no namespace, a -> @any/nested' => [
                ['a' => '@any/nested'],
                ['qbeced67e6b340abc67a397f6e90bb0' => 'a', 'first__aa' => 'aa', 'first__second__aaa' => 'aaa', 'a9d66412d169b85537e11d9e49b75f9' => '@any'],
            ],
        ];
    }

    #[DataProvider('parametersDataProvider')]
    #[Test]
    public function parametersAreProcessed(array $arguments, array $deflatedParameters): void
    {
        $subject = new VariableProcessor(new VariableProcessorCache());
        $inflatedParameters = ['a' => 'a', 'first' => ['aa' => 'aa', 'second' => ['aaa' => 'aaa', '@any' => '@any']]];
        self::assertEquals(
            $deflatedParameters,
            $subject->deflateParameters($inflatedParameters, $arguments)
        );
        self::assertEquals(
            $inflatedParameters,
            $subject->inflateParameters($deflatedParameters, $arguments)
        );
    }

    public static function namespaceParametersDataProvider(): array
    {
        return [
            // no changes expected without having a non-empty namespace
            'no namespace, no arguments' => [
                '',
                [],
                ['a' => 'a', 'first' => ['aa' => 'aa', 'second' => ['aaa' => 'aaa', '@any' => '@any']]],
            ],
            'no namespace, a -> 1' => [
                '',
                ['a' => 1],
                ['a' => 'a', 'first' => ['aa' => 'aa', 'second' => ['aaa' => 'aaa', '@any' => '@any']]],
            ],
            'no namespace, a -> newA' => [
                '',
                ['a' => 'newA'],
                ['a' => 'a', 'first' => ['aa' => 'aa', 'second' => ['aaa' => 'aaa', '@any' => '@any']]],
            ],
            'no namespace, a -> @any/nested' => [
                '',
                ['a' => '@any/nested'],
                ['a' => 'a', 'first' => ['aa' => 'aa', 'second' => ['aaa' => 'aaa', '@any' => '@any']]],
            ],
            // changes for namespace 'first' are expected
            'first, no arguments' => [
                'first',
                [],
                ['a' => 'a', 'first__aa' => 'aa', 'first__second__aaa' => 'aaa', 'a9d66412d169b85537e11d9e49b75f9' => '@any'],
            ],
            'first, aa -> newAA' => [
                'first',
                ['aa' => 'newAA'],
                ['a' => 'a', 'first__newAA' => 'aa', 'first__second__aaa' => 'aaa', 'a9d66412d169b85537e11d9e49b75f9' => '@any'],
            ],
            'first, second -> newSecond' => [
                'first',
                ['second' => 'newSecond'],
                ['a' => 'a', 'first__aa' => 'aa', 'first__newSecond__aaa' => 'aaa', 'q7aded81f5d1607191c695720db7ab2' => '@any'],
            ],
            'first, aa -> any/nested' => [
                'first',
                ['aa' => 'any/nested'],
                ['a' => 'a', 'first__any__nested' => 'aa', 'first__second__aaa' => 'aaa', 'a9d66412d169b85537e11d9e49b75f9' => '@any'],
            ],
            'first, aa -> @any/nested' => [
                'first',
                ['aa' => '@any/nested'],
                ['a' => 'a', 'ab0ce8f9f822228b4f324ec38b9c038' => 'aa', 'first__second__aaa' => 'aaa', 'a9d66412d169b85537e11d9e49b75f9' => '@any'],
            ],
            'first, aa -> newAA, second => newSecond' => [
                'first',
                ['aa' => 'newAA', 'second' => 'newSecond'],
                ['a' => 'a', 'first__newAA' => 'aa', 'first__newSecond__aaa' => 'aaa', 'q7aded81f5d1607191c695720db7ab2' => '@any'],
            ],
        ];
    }

    #[DataProvider('namespaceParametersDataProvider')]
    #[Test]
    public function namespaceParametersAreProcessed(string $namespace, array $arguments, array $deflatedParameters): void
    {
        $subject = new VariableProcessor(new VariableProcessorCache());
        $inflatedParameters = ['a' => 'a', 'first' => ['aa' => 'aa', 'second' => ['aaa' => 'aaa', '@any' => '@any']]];
        self::assertEquals(
            $deflatedParameters,
            $subject->deflateNamespaceParameters($inflatedParameters, $namespace, $arguments)
        );
        self::assertEquals(
            $inflatedParameters,
            $subject->inflateNamespaceParameters($deflatedParameters, $namespace, $arguments)
        );
    }

    public static function keysDataProvider(): array
    {
        return array_merge(
            self::regularKeysDataProvider(),
            self::specialKeysDataProvider()
        );
    }

    public static function regularKeysDataProvider(): array
    {
        return [
            'no arguments, no namespace' => [
                null,
                [],
                ['a' => 'a', 'b' => 'b', 'c' => ['d' => 'd', 'e' => 'e']],
            ],
            'a -> 1, no namespace' => [
                null,
                ['a' => 1],
                [1 => 'a', 'b' => 'b', 'c' => ['d' => 'd', 'e' => 'e']],
            ],
            'a -> newA, no namespace' => [
                null,
                ['a' => 'newA'],
                ['newA' => 'a', 'b' => 'b', 'c' => ['d' => 'd', 'e' => 'e']],
            ],
            'no arguments, first' => [
                'first',
                [],
                ['first__a' => 'a', 'first__b' => 'b', 'first__c' => ['d' => 'd', 'e' => 'e']],
            ],
            'a -> newA, first' => [
                'first',
                ['a' => 'newA'],
                ['first__newA' => 'a', 'first__b' => 'b', 'first__c' => ['d' => 'd', 'e' => 'e']],
            ],
            'a -> any/nested, first' => [
                'first',
                ['a' => 'any/nested'],
                ['first__any__nested' => 'a', 'first__b' => 'b', 'first__c' => ['d' => 'd', 'e' => 'e']],
            ],
            'd -> newD, first' => [
                'first',
                ['d' => 'newD'], // not substituted, which is expected
                ['first__a' => 'a', 'first__b' => 'b', 'first__c' => ['d' => 'd', 'e' => 'e']],
            ],
        ];
    }

    public static function specialKeysDataProvider(): array
    {
        return [
            'a -> @any/nested, no namespace' => [
                null,
                ['a' => '@any/nested'],
                ['qbeced67e6b340abc67a397f6e90bb0' => 'a', 'b' => 'b', 'c' => ['d' => 'd', 'e' => 'e']],
            ],
            'a -> newA, namespace_being_longer_than_32_characters' => [
                'namespace_being_longer_than_32_characters',
                ['a' => 'newA'],
                ['qaea1f31c57b9c3e78c8205838d4563' => 'a', 'ub5e2989b61a4964ba4e06fc6de8527' => 'b', 'oabf16f448f7b02c6ecb13d155e5a4b' => ['d' => 'd', 'e' => 'e']],
            ],
            'a -> @any/nested, first' => [
                'first',
                ['a' => '@any/nested'],
                ['ab0ce8f9f822228b4f324ec38b9c038' => 'a', 'first__b' => 'b', 'first__c' => ['d' => 'd', 'e' => 'e']],
            ],
        ];
    }

    #[DataProvider('keysDataProvider')]
    #[Test]
    public function keysAreDeflated(?string $namespace, array $arguments, array $deflatedKeys): void
    {
        $subject = new VariableProcessor(new VariableProcessorCache());
        $inflatedKeys = ['a' => 'a', 'b' => 'b', 'c' => ['d' => 'd', 'e' => 'e']];
        self::assertEquals(
            $deflatedKeys,
            $subject->deflateKeys($inflatedKeys, $namespace, $arguments)
        );
        self::assertEquals(
            $inflatedKeys,
            $subject->inflateKeys($deflatedKeys, $namespace, $arguments)
        );
    }

    #[DataProvider('specialKeysDataProvider')]
    #[Test]
    public function specialKeysAreNotInflatedWithoutBeingDeflated(?string $namespace, array $arguments, array $deflatedKeys): void
    {
        $this->expectException(\OutOfRangeException::class);
        $this->expectExceptionCode(1537633463);
        (new VariableProcessor(new VariableProcessorCache()))->inflateKeys($deflatedKeys, $namespace, $arguments);
    }
}
