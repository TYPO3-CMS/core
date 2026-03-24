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

namespace TYPO3\CMS\Core\Schema\Struct;

use TYPO3\CMS\Core\Schema\Field\FieldCollection;

final readonly class WizardStep
{
    public function __construct(
        private string $identifier,
        private string $title,
        private FieldCollection $fields,
    ) {}

    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getFields(): FieldCollection
    {
        return $this->fields;
    }

    public static function __set_state(array $state): self
    {
        return new self(...$state);
    }
}
