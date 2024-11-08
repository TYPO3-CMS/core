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

namespace TYPO3\CMS\Core\PageTitle;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use TYPO3\CMS\Core\Service\DependencyOrderingService;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\TypoScript\TypoScriptService;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * This class will take care of the different providers and returns the title with the highest priority
 */
class PageTitleProviderManager implements SingletonInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    private array $pageTitleCache = [];

    public function __construct(
        private readonly ContainerInterface $container,
        private readonly DependencyOrderingService $dependencyOrderingService,
        private readonly TypoScriptService $typoScriptService,
    ) {}

    public function getTitle(ServerRequestInterface $request): string
    {
        $pageTitle = '';

        $titleProviders = $this->getPageTitleProviderConfiguration($request);
        $titleProviders = $this->setProviderOrder($titleProviders);
        $orderedTitleProviders = $this->dependencyOrderingService->orderByDependencies($titleProviders);

        $this->logger->debug('Page title providers ordered', [
            'orderedTitleProviders' => $orderedTitleProviders,
        ]);

        foreach ($orderedTitleProviders as $configuration) {
            if (is_subclass_of($configuration['provider'] ?? null, PageTitleProviderInterface::class)) {
                /** @var PageTitleProviderInterface $titleProviderObject */
                $titleProviderObject = $this->container->get($configuration['provider']);
                if (method_exists($titleProviderObject, 'setRequest')) {
                    $titleProviderObject->setRequest($request);
                }
                if (($pageTitle = $titleProviderObject->getTitle())
                    || ($pageTitle = $this->pageTitleCache[$configuration['provider']] ?? '') !== ''
                ) {
                    $this->logger->debug('Page title provider {provider} used on page {title}', [
                        'title' => $pageTitle,
                        'provider' => $configuration['provider'],
                    ]);
                    $this->pageTitleCache[$configuration['provider']] = $pageTitle;
                    break;
                }
                $this->logger->debug('Page title provider {provider} skipped on page {title}', [
                    'title' => $pageTitle,
                    'provider' => $configuration['provider'],
                    'providerUsed' => $configuration['provider'],
                ]);
            }
        }

        return $pageTitle;
    }

    /**
     * @internal
     */
    public function getPageTitleCache(): array
    {
        return $this->pageTitleCache;
    }

    /**
     * @internal
     */
    public function setPageTitleCache(array $pageTitleCache): void
    {
        $this->pageTitleCache = $pageTitleCache;
    }

    /**
     * Get the TypoScript configuration for pageTitleProviders
     */
    private function getPageTitleProviderConfiguration(ServerRequestInterface $request): array
    {
        $config = $this->typoScriptService->convertTypoScriptArrayToPlainArray(
            $request->getAttribute('frontend.typoscript')->getConfigArray()
        );
        return $config['pageTitleProviders'] ?? [];
    }

    /**
     * @return string[]
     * @throws \UnexpectedValueException
     */
    protected function setProviderOrder(array $orderInformation): array
    {
        foreach ($orderInformation as $provider => &$configuration) {
            if (isset($configuration['before'])) {
                if (is_string($configuration['before'])) {
                    $configuration['before'] = GeneralUtility::trimExplode(',', $configuration['before'], true);
                } elseif (!is_array($configuration['before'])) {
                    throw new \UnexpectedValueException(
                        'The specified "before" order configuration for provider "' . $provider . '" is invalid.',
                        1535803185
                    );
                }
            }
            if (isset($configuration['after'])) {
                if (is_string($configuration['after'])) {
                    $configuration['after'] = GeneralUtility::trimExplode(',', $configuration['after'], true);
                } elseif (!is_array($configuration['after'])) {
                    throw new \UnexpectedValueException(
                        'The specified "after" order configuration for provider "' . $provider . '" is invalid.',
                        1535803186
                    );
                }
            }
        }
        return $orderInformation;
    }
}
