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
 * using the "x" unit (relative pixel density, like "1x" or "2x")
 */
final class DensitySrcsetCandidate extends SrcsetCandidate
{
    public function __construct(
        protected float $density,
        protected ?int $referenceWidth = null
    ) {}

    public function getDensity(): float
    {
        return $this->density;
    }

    public function setDensity(float $density): static
    {
        $this->density = $density;
        return $this;
    }

    public function getReferenceWidth(): ?int
    {
        return $this->referenceWidth;
    }

    /**
     * The absolute $referenceWidth will be used as "1x" width.
     */
    public function setReferenceWidth(int $referenceWidth): static
    {
        $this->referenceWidth = $referenceWidth;
        return $this;
    }

    public function getDescriptor(): string
    {
        return $this->density . static::DENSITY_UNIT;
    }

    public function getCalculatedWidth(): int
    {
        if ($this->referenceWidth === null) {
            throw new \InvalidArgumentException(sprintf(
                'Reference width needs to be specified if pixel density descriptors (e. g. 2x) are used in srcset: %s',
                $this->getDescriptor()
            ), 1697743145);
        }
        return (int)($this->density * $this->referenceWidth);
    }
}
