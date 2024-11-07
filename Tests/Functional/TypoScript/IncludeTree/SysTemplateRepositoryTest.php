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

namespace TYPO3\CMS\Core\Tests\Functional\TypoScript\IncludeTree;

use PHPUnit\Framework\Attributes\Test;
use TYPO3\CMS\Core\TypoScript\IncludeTree\SysTemplateRepository;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

final class SysTemplateRepositoryTest extends FunctionalTestCase
{
    #[Test]
    public function emptyRootline(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/SysTemplate/singleRootTemplate.csv');
        $rootline = [];
        $sysTemplateRepository = $this->get(SysTemplateRepository::class);
        $result = $sysTemplateRepository->getSysTemplateRowsByRootline($rootline);
        self::assertSame([], $result);
    }

    #[Test]
    public function singleRootTemplate(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/SysTemplate/singleRootTemplate.csv');
        $rootline = [
            [
                'uid' => 1,
                'pid' => 0,
                'is_siteroot' => 0,
            ],
        ];
        $sysTemplateRepository = $this->get(SysTemplateRepository::class);
        $result = $sysTemplateRepository->getSysTemplateRowsByRootline($rootline);
        self::assertSame(1, $result[0]['uid']);
        $result = $sysTemplateRepository->getSysTemplateRowsByRootlineWithUidOverride($rootline, null, 1);
        self::assertSame(1, $result[0]['uid']);
    }

    #[Test]
    public function twoPagesTwoTemplates(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/SysTemplate/twoPagesTwoTemplates.csv');
        $rootline = [
            [
                'uid' => 2,
                'pid' => 1,
                'is_siteroot' => 0,
            ],
            [
                'uid' => 1,
                'pid' => 0,
                'is_siteroot' => 0,
            ],
        ];
        $sysTemplateRepository = $this->get(SysTemplateRepository::class);
        $result = $sysTemplateRepository->getSysTemplateRowsByRootline($rootline);
        self::assertSame(1, $result[0]['uid']);
        self::assertSame(2, $result[1]['uid']);
        $result = $sysTemplateRepository->getSysTemplateRowsByRootlineWithUidOverride($rootline, null, 2);
        self::assertSame(1, $result[0]['uid']);
        self::assertSame(2, $result[1]['uid']);
    }

    #[Test]
    public function twoTemplatesOnPagePrefersTheOneWithLowerSorting(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/SysTemplate/twoTemplatesOnPage.csv');
        $rootline = [
            [
                'uid' => 1,
                'pid' => 0,
                'is_siteroot' => 0,
            ],
        ];
        $sysTemplateRepository = $this->get(SysTemplateRepository::class);
        $result = $sysTemplateRepository->getSysTemplateRowsByRootline($rootline);
        self::assertSame(1, $result[0]['uid']);
        $result = $sysTemplateRepository->getSysTemplateRowsByRootlineWithUidOverride($rootline, null, 2);
        self::assertSame(2, $result[0]['uid']);
    }
}
