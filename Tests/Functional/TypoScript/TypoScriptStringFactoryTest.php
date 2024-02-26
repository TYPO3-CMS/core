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

namespace TYPO3\CMS\Core\Tests\Functional\TypoScript;

use PHPUnit\Framework\Attributes\Test;
use TYPO3\CMS\Core\EventDispatcher\NoopEventDispatcher;
use TYPO3\CMS\Core\TypoScript\AST\AstBuilder;
use TYPO3\CMS\Core\TypoScript\AST\Node\ChildNode;
use TYPO3\CMS\Core\TypoScript\AST\Node\RootNode;
use TYPO3\CMS\Core\TypoScript\TypoScriptStringFactory;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

final class TypoScriptStringFactoryTest extends FunctionalTestCase
{
    protected bool $initializeDatabase = false;

    #[Test]
    public function parseFromStringWithIncludesParsesImport(): void
    {
        $expected = new RootNode();
        $fooNode = new ChildNode('foo');
        $fooNode->setValue('fooValue');
        $expected->addChild($fooNode);
        $subject = $this->get(TypoScriptStringFactory::class);
        $result = $subject->parseFromStringWithIncludes(
            'testing',
            '@import \'EXT:core/Tests/Functional/TypoScript/Fixtures/typoScriptStringTextFixture.typoscript\''
        );
        self::assertEquals($expected, $result);
    }

    #[Test]
    public function parseFromStringParsesSimpleString(): void
    {
        $expected = new RootNode();
        $fooNode = new ChildNode('foo');
        $fooNode->setValue('bar');
        $expected->addChild($fooNode);
        $subject = $this->get(TypoScriptStringFactory::class);
        $result = $subject->parseFromString('foo = bar', new AstBuilder(new NoopEventDispatcher()));
        self::assertEquals($expected, $result);
    }
}
