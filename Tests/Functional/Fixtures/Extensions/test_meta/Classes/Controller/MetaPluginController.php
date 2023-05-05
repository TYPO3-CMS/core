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

namespace TYPO3Tests\TestMeta\Controller;

use TYPO3\CMS\Core\MetaTag\MetaTagManagerRegistry;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Tests\TestMeta\PageTitle\CustomPageTitleProvider;

class MetaPluginController
{
    public function setMetaData(string $content, array $configuration): string
    {
        $pageId = $GLOBALS['TYPO3_REQUEST']->getQueryParams()['id'];
        if (!empty($configuration['setTitle'])) {
            GeneralUtility::makeInstance(CustomPageTitleProvider::class)
                          ->setTitle('static title with pageId: ' . $pageId . ' and pluginNumber: ' . $configuration['pluginNumber']);
        }
        $metaTagManager = GeneralUtility::makeInstance(MetaTagManagerRegistry::class)->getManagerForProperty('og:title');
        $metaTagManager->addProperty(
            'og:title',
            'OG title from a controller with pageId: ' . $pageId . ' and pluginNumber: ' . $configuration['pluginNumber'],
            [],
            true
        );
        return 'TYPO3Tests\TestMeta\Controller::setMetaData';
    }
}
