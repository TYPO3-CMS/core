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
use TYPO3\CMS\Core\Database\Schema\Parser\AST\DataType\EnumDataType;
use TYPO3\CMS\Core\Tests\Unit\Database\Schema\Parser\AbstractDataTypeBaseTestCase;

/**
 * Tests for parsing ENUM SQL data type
 */
final class EnumDataTypeTest extends AbstractDataTypeBaseTestCase
{
    /**
     * Data provider for canParseEnumDataType()
     */
    public static function canParseEnumDataTypeProvider(): array
    {
        return [
            'ENUM(value)' => [
                "ENUM('value1')",
                EnumDataType::class,
                ['value1'],
            ],
            'ENUM(value,value)' => [
                "ENUM('value1','value2')",
                EnumDataType::class,
                ['value1', 'value2'],
            ],
            'ENUM(value, value)' => [
                "ENUM('value1', 'value2')",
                EnumDataType::class,
                ['value1', 'value2'],
            ],
        ];
    }

    #[DataProvider('canParseEnumDataTypeProvider')]
    #[Test]
    public function canParseDataType(string $columnDefinition, string $className, array $values): void
    {
        $subject = $this->createSubject($columnDefinition);

        self::assertInstanceOf($className, $subject->dataType);
        self::assertSame($values, $subject->dataType->getValues());
    }
}
