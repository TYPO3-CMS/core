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

namespace TYPO3\CMS\Core\Tests\Unit\Database\Schema\Parser\DataTypes;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\CMS\Core\Database\Schema\Parser\AST\DataType\LongTextDataType;
use TYPO3\CMS\Core\Database\Schema\Parser\AST\DataType\MediumTextDataType;
use TYPO3\CMS\Core\Database\Schema\Parser\AST\DataType\TextDataType;
use TYPO3\CMS\Core\Database\Schema\Parser\AST\DataType\TinyTextDataType;
use TYPO3\CMS\Core\Tests\Unit\Database\Schema\Parser\AbstractDataTypeBaseTestCase;

/**
 * Tests for parsing TEXT SQL data types
 */
final class TextTypesTest extends AbstractDataTypeBaseTestCase
{
    /**
     * Data provider for canParseTextDataType()
     */
    public static function canParseTextDataTypeProvider(): array
    {
        return [
            'TINYTEXT' => [
                'TINYTEXT',
                TinyTextDataType::class,
            ],
            'TEXT' => [
                'TEXT',
                TextDataType::class,
            ],
            'MEDIUMTEXT' => [
                'MEDIUMTEXT',
                MediumTextDataType::class,
            ],
            'LONGTEXT' => [
                'LONGTEXT',
                LongTextDataType::class,
            ],
        ];
    }

    #[DataProvider('canParseTextDataTypeProvider')]
    #[Test]
    public function canParseDataType(string $columnDefinition, string $className): void
    {
        $subject = $this->createSubject($columnDefinition);

        self::assertInstanceOf($className, $subject->dataType);
    }
}
