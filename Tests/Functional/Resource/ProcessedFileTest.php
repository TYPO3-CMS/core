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

namespace TYPO3\CMS\Core\Tests\Functional\Resource;

use PHPUnit\Framework\Attributes\DoesNotPerformAssertions;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\CMS\Core\Localization\LanguageServiceFactory;
use TYPO3\CMS\Core\Resource\ProcessedFile;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

final class ProcessedFileTest extends FunctionalTestCase
{
    private const TEST_IMAGE = 'fileadmin/ProcessedFileTest.jpg';

    /**
     * @var array<string, non-empty-string>
     */
    protected array $pathsToProvideInTestInstance = [
        'typo3/sysext/core/Tests/Functional/Resource/Fixtures/ProcessedFileTest.jpg' => 'fileadmin/ProcessedFileTest.jpg',
    ];

    public function setUp(): void
    {
        parent::setUp();
        $this->importCSVDataSet(__DIR__ . '/../Fixtures/be_users.csv');
        $backendUser = $this->setUpBackendUser(1);
        $GLOBALS['LANG'] = $this->get(LanguageServiceFactory::class)->createFromUserPreferences($backendUser);
    }

    #[Test]
    #[DoesNotPerformAssertions]
    public function processedFileArrayCanBeSerialized(): void
    {
        $resourceFactory = $this->get(ResourceFactory::class);
        $originalFile = $resourceFactory->retrieveFileOrFolderObject(self::TEST_IMAGE);
        $someProcessedFile = new ProcessedFile(
            $originalFile,
            ProcessedFile::CONTEXT_IMAGECROPSCALEMASK,
            []
        );
        $processedFile = new ProcessedFile(
            $originalFile,
            ProcessedFile::CONTEXT_IMAGECROPSCALEMASK,
            [
                'width' => '2000c',
                'height' => '300c-60',
                'm' => [
                    'bgImg' => $someProcessedFile,
                    'mask' => $someProcessedFile,
                ],
            ],
        );
        serialize($processedFile->toArray());
    }
}
