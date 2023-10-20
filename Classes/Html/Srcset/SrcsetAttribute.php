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
 * Generates a HTML srcset attribute for responsive images
 */
final class SrcsetAttribute
{
    /** @var SrcsetCandidate[] */
    private array $candidates = [];
    private string $candidateType;

    /**
     * @param string[]  $descriptors     Array of srcset width (like "300w, 500w") or density (like "1x, 2x")
     *                                   descriptors.
     * @param int|null  $referenceWidth  Needs to be provided if $descriptors contains density descriptors
     *                                   (like "2x") to be able to resolve the relative image dimensions.
     *                                   The absolute $referenceWidth will be used as "1x" width.
     */
    public static function createFromDescriptors(array $descriptors, ?int $referenceWidth = null): SrcsetAttribute
    {
        $generator = new SrcsetAttribute();
        foreach ($descriptors as $descriptor) {
            $generator->addCandidate(SrcsetCandidate::createFromDescriptor((string)$descriptor, $referenceWidth));
        }
        return $generator;
    }

    public function addCandidate(SrcsetCandidate $candidate): static
    {
        if (!$this->isValidCandidate($candidate)) {
            throw new \InvalidArgumentException(sprintf(
                'Invalid mix of w and x descriptors in srcset: %s, ..., %s',
                $this->candidates[0]->generateSrcset(),
                $candidate->generateSrcset()
            ), 1697745459);
        }

        $this->candidates[] = $candidate;
        return $this;
    }

    /**
     * @return SrcsetCandidate[]
     */
    public function getCandidates(): array
    {
        return $this->candidates;
    }

    public function generateSrcset(): string
    {
        $uniqueSrcset = [];
        foreach ($this->candidates as $candidate) {
            $uniqueSrcset[$candidate->getDescriptor()] = $candidate->generateSrcset();
        }
        return implode(', ', $uniqueSrcset);
    }

    public function __toString()
    {
        return $this->generateSrcset();
    }

    private function isValidCandidate(SrcsetCandidate $candidate): bool
    {
        if (!isset($this->candidateType)) {
            $this->candidateType = ($candidate instanceof DensitySrcsetCandidate)
                ? DensitySrcsetCandidate::class
                : WidthSrcsetCandidate::class;
            return true;
        }

        return $candidate instanceof $this->candidateType;
    }
}
