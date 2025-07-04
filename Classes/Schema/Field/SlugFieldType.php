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

namespace TYPO3\CMS\Core\Schema\Field;

final readonly class SlugFieldType extends AbstractFieldType
{
    public function getType(): string
    {
        return 'slug';
    }

    public function isSearchable(): bool
    {
        return (bool)($this->configuration['searchable'] ?? true);
    }

    public function getGeneratorOption(string $optionName): array|string|bool|null
    {
        return $this->configuration['generatorOptions'][$optionName] ?? null;
    }

    public function getGeneratorOptions(): array
    {
        return is_array($this->configuration['generatorOptions']) ? $this->configuration['generatorOptions'] : [];
    }

    public function hasDefaultValue(): true
    {
        return true;
    }
    public function getDefaultValue(): string
    {
        return '';
    }

    public function isNullable(): false
    {
        return false;
    }
}
