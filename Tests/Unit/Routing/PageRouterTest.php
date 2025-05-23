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

namespace TYPO3\CMS\Core\Tests\Unit\Routing;

use PHPUnit\Framework\Attributes\Test;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\Routing\BackendEntryPointResolver;
use TYPO3\CMS\Core\Routing\PageArguments;
use TYPO3\CMS\Core\Routing\PageRouter;
use TYPO3\CMS\Core\Routing\PageSlugCandidateProvider;
use TYPO3\CMS\Core\Routing\RequestContextFactory;
use TYPO3\CMS\Core\Routing\RouteNotFoundException;
use TYPO3\CMS\Core\Routing\SiteRouteResult;
use TYPO3\CMS\Core\Schema\TcaSchemaFactory;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

final class PageRouterTest extends UnitTestCase
{
    protected bool $resetSingletonInstances = true;

    protected function setUp(): void
    {
        $requestContextFactory = new RequestContextFactory(new BackendEntryPointResolver());
        GeneralUtility::addInstance(RequestContextFactory::class, $requestContextFactory);
        parent::setUp();
    }

    #[Test]
    public function matchRequestThrowsExceptionIfNoPreviousResultGiven(): void
    {
        $this->expectException(RouteNotFoundException::class);
        $this->expectExceptionCode(1555303496);
        $incomingUrl = 'https://king.com/lotus-flower/en/mr-magpie/bloom';
        $request = new ServerRequest($incomingUrl, 'GET');
        $subject = new PageRouter(new Site('lotus-flower', 13, []));
        $subject->matchRequest($request);
    }

    #[Test]
    public function properSiteConfigurationFindsRoute(): void
    {
        $incomingUrl = 'https://king.com/lotus-flower/en/mr-magpie/bloom';
        $pageRecord = ['uid' => 13, 'l10n_parent' => 0, 'slug' => '/mr-magpie/bloom', 'sys_language_uid' => 0];
        $site = new Site('lotus-flower', 13, [
            'base' => '/lotus-flower/',
            'languages' => [
                0 => [
                    'languageId' => 0,
                    'locale' => 'en_US.UTF-8',
                    'base' => '/en/',
                ],
            ],
        ]);
        $language = $site->getDefaultLanguage();

        $pageSlugCandidateProvider = $this->createMock(PageSlugCandidateProvider::class);
        $pageSlugCandidateProvider->method('getCandidatesForPath')->with('/mr-magpie/bloom', $language)->willReturn([$pageRecord]);
        GeneralUtility::addInstance(PageSlugCandidateProvider::class, $pageSlugCandidateProvider);
        GeneralUtility::addInstance(TcaSchemaFactory::class, $this->createMock(TcaSchemaFactory::class));

        $request = new ServerRequest($incomingUrl, 'GET');
        $previousResult = new SiteRouteResult($request->getUri(), $site, $language, '/mr-magpie/bloom');
        $routeResult = (new PageRouter($site))->matchRequest($request, $previousResult);

        $expectedRouteResult = new PageArguments(13, '0', [], [], []);
        self::assertEquals($expectedRouteResult, $routeResult);
    }

    /**
     * Let's see if the slug is "/blabla" and the base does not have a trailing slash ("/en")
     */
    #[Test]
    public function properSiteConfigurationWithoutTrailingSlashFindsRoute(): void
    {
        $incomingUrl = 'https://king.com/lotus-flower/en/mr-magpie/bloom';
        $pageRecord = ['uid' => 13, 'l10n_parent' => 0, 'slug' => '/mr-magpie/bloom', 'sys_language_uid' => 0];
        $site = new Site('lotus-flower', 13, [
            'base' => '/lotus-flower/',
            'languages' => [
                0 => [
                    'languageId' => 0,
                    'locale' => 'en_US.UTF-8',
                    'base' => '/en',
                ],
            ],
        ]);
        $language = $site->getDefaultLanguage();
        $pageSlugCandidateProvider = $this->createMock(PageSlugCandidateProvider::class);
        $pageSlugCandidateProvider->method('getCandidatesForPath')->with(self::anything())->willReturn([$pageRecord]);
        GeneralUtility::addInstance(PageSlugCandidateProvider::class, $pageSlugCandidateProvider);
        GeneralUtility::addInstance(TcaSchemaFactory::class, $this->createMock(TcaSchemaFactory::class));

        $request = new ServerRequest($incomingUrl, 'GET');
        $previousResult = new SiteRouteResult($request->getUri(), $site, $language, '/mr-magpie/bloom/');
        $routeResult = (new PageRouter($site))->matchRequest($request, $previousResult);

        $expectedRouteResult = new PageArguments((int)$pageRecord['uid'], '0', []);
        self::assertEquals($expectedRouteResult, $routeResult);
    }
}
