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

namespace TYPO3\CMS\Core\RateLimiter;

use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\RateLimiter\LimiterInterface;

/**
 * TYPO3's own rate limiter factory interface extending Symfony's RateLimiterFactoryInterface
 * with additional convenience methods for request-based and login rate limiting.
 */
interface RateLimiterFactoryInterface extends \Symfony\Component\RateLimiter\RateLimiterFactoryInterface
{
    /**
     * Create a limiter with custom configuration.
     */
    public function createLimiter(array $config, ?string $key = null): LimiterInterface;

    /**
     * Create a limiter based on the request input.
     */
    public function createRequestBasedLimiter(ServerRequestInterface $request, array $configuration): LimiterInterface;

    /**
     * Create a limiter for user login.
     */
    public function createLoginRateLimiter(ServerRequestInterface $request, string $loginType): LimiterInterface;
}
