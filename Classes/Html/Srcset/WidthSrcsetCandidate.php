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

namespace TYPO3\CMS\Core\Html\Srcset;

/**
 * Represents a source candidate for the HTML srcset attribute
 * using the "w" unit (absolute width in image pixels, like "200w")
 */
final class WidthSrcsetCandidate extends SrcsetCandidate
{
    public function __construct(protected int $width) {}

    public function setWidth(int $width): static
    {
        $this->width = $width;
        return $this;
    }

    public function getCalculatedWidth(): int
    {
        return $this->width;
    }

    public function getDescriptor(): string
    {
        return $this->width . static::WIDTH_UNIT;
    }
}
