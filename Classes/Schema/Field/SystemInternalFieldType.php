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

/**
 * This is used for system-internal fields that haven't been defined in the "columns"
 * but need a representation in some areas such as "label_alt".
 * @internal This is an experimental implementation.
 */
final readonly class SystemInternalFieldType extends AbstractFieldType
{
    public function getType(): string
    {
        return '';
    }

    public function isSearchable(): false
    {
        return false;
    }
}
