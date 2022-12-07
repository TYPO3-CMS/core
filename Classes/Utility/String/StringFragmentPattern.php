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

namespace TYPO3\CMS\Core\Utility\String;

/**
 * @internal
 */
class StringFragmentPattern
{
    /**
     * @var string
     */
    protected $type;

    /**
     * @var string
     */
    protected $pattern;

    public function __construct(string $type, string $pattern)
    {
        $this->type = $type;
        $this->pattern = $pattern;
    }

    /**
     * Compiles this string fragment pattern to a scoped PCRE pattern string.
     */
    public function compilePattern(): string
    {
        return sprintf(
            '(?P<%s>%s)',
            $this->type . '_' . bin2hex(random_bytes(5)),
            $this->pattern
        );
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getPattern(): string
    {
        return $this->pattern;
    }
}
