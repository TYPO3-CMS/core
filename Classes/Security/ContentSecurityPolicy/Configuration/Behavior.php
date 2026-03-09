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

namespace TYPO3\CMS\Core\Security\ContentSecurityPolicy\Configuration;

/**
 * @internal
 */
final class Behavior
{
    /**
     * @param bool|null $useNonce Whether to use nonce values
     * @param bool|null $useHash Whether to use hash values
     */
    public function __construct(
        /**
         * Controls nonce usage. null = system decides per context, true = always use nonce, false = never use nonce.
         */
        public ?bool $useNonce = null,

        /**
         * Whether to collect CSP hash values for assets. Always true by default because hashes enable
         * response caching (unlike nonces which are per-request). Even when nonces are used, hashes are
         * still collected so that cached responses include the correct CSP directives.
         */
        public ?bool $useHash = null,
    ) {}

    /**
     * Creates a Behavior instance from a `csp.yaml` `behavior:` section.
     *
     * Example:
     * ```yaml
     * behavior:
     *   useNonce: false
     *   useHash: true
     * ```
     */
    public static function fromArray(array $data): self
    {
        $useNonce = isset($data['useNonce']) ? (bool)$data['useNonce'] : null;
        $useHash = isset($data['useHash']) ? (bool)$data['useHash'] : null;
        return new self($useNonce, $useHash);
    }
}
