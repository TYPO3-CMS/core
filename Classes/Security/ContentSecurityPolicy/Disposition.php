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

namespace TYPO3\CMS\Core\Security\ContentSecurityPolicy;

/**
 * Representation of Content-Security-Policy disposition
 * see https://developer.mozilla.org/en-US/docs/Web/HTTP/CSP#disposition
 */
enum Disposition: string
{
    case enforce = 'enforce';
    case report = 'report';

    public function getHttpHeaderName(): string
    {
        return match ($this) {
            self::enforce => 'Content-Security-Policy',
            self::report => 'Content-Security-Policy-Report-Only',
        };
    }
}
