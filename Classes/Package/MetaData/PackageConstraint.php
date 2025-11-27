<?php

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

namespace TYPO3\CMS\Core\Package\MetaData;

use Composer\Semver\Constraint\ConstraintInterface;
use Composer\Semver\Constraint\MatchAllConstraint;
use Composer\Semver\VersionParser;

/**
 * Package constraint meta model
 */
class PackageConstraint
{
    private ?ConstraintInterface $constraint = null;

    public function __construct(
        protected readonly string $constraintType,
        protected readonly string $value,
        protected ?string $minVersion = null,
        protected ?string $maxVersion = null,
        protected ?string $versionConstraints = null,
    ) {}

    private function initConstraint(): void
    {
        if ($this->constraint !== null) {
            return;
        }
        if ($this->versionConstraints === null) {
            if ($this->minVersion !== null || $this->maxVersion !== null) {
                $this->versionConstraints = sprintf('%s - %s', $this->minVersion, $this->maxVersion);
            } else {
                $constraint = new MatchAllConstraint();
            }
        }
        try {
            $versionParser = new VersionParser();
            $constraint ??= $versionParser->parseConstraints($this->versionConstraints);
        } catch (\UnexpectedValueException) {
            $constraint = new MatchAllConstraint();
        }
        $this->constraint = $constraint;
        $this->calculateMinMaxVersion();

    }

    private function calculateMinMaxVersion(): void
    {
        $this->minVersion = $this->prettyVersion($this->constraint->getLowerBound()->getVersion());
        $upperBound = $this->constraint->getUpperBound();
        $maxVersion = $upperBound->getVersion();
        if (!$this->constraint instanceof MatchAllConstraint && !$upperBound->isInclusive()) {
            [$major, $minor, $patch] = explode('.', $upperBound->getVersion());
            if ($minor === '0' && $patch === '0') {
                $minor = $patch = '999';
                $major = (int)$major - 1;
            }
            if ($patch === '0') {
                $patch = '999';
                $minor = (int)$minor - 1;
            }
            $maxVersion = sprintf('%s.%s.%s', $major, $minor, $patch);
        }
        $this->maxVersion = $maxVersion;
    }

    private function prettyVersion(string $normalizedVersion): string
    {
        [$major, $minor, $patch] = explode('.', $normalizedVersion);
        return sprintf('%s.%s.%s', $major, $minor, $patch);
    }

    /**
     * @return string The constraint name or value
     */
    public function getValue(): string
    {
        return $this->value;
    }

    public function getVersionRange(): string
    {
        $this->initConstraint();
        return sprintf('%s - %s', $this->minVersion, $this->maxVersion);
    }

    /**
     * @return string The constraint type (depends, conflicts, suggests)
     */
    public function getConstraintType(): string
    {
        return $this->constraintType;
    }
}
