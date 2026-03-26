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

namespace TYPO3\CMS\Core\Log\Processor;

use TYPO3\CMS\Core\Http\NormalizedParams;
use TYPO3\CMS\Core\Log\LogRecord;

/**
 * Web log processor to automatically add web request related data to a log
 * record.
 */
class WebProcessor extends AbstractProcessor
{
    public function processLogRecord(LogRecord $logRecord): LogRecord
    {
        $normalizedParams = ($GLOBALS['TYPO3_REQUEST'] ?? null)?->getAttribute('normalizedParams');
        if ($normalizedParams instanceof NormalizedParams) {
            $logRecord->addData([
                'HTTP_HOST' => $normalizedParams->getHttpHost(),
                'TYPO3_HOST_ONLY' => $normalizedParams->getRequestHostOnly(),
                'TYPO3_PORT' => $normalizedParams->getRequestPort(),
                'PATH_INFO' => $normalizedParams->getPathInfo(),
                'QUERY_STRING' => $normalizedParams->getQueryString(),
                'REQUEST_URI' => $normalizedParams->getRequestUri(),
                'HTTP_REFERER' => $normalizedParams->getHttpReferer(),
                'TYPO3_REQUEST_HOST' => $normalizedParams->getRequestHost(),
                'TYPO3_REQUEST_URL' => $normalizedParams->getRequestUrl(),
                'TYPO3_REQUEST_SCRIPT' => $normalizedParams->getRequestScript(),
                'TYPO3_REQUEST_DIR' => $normalizedParams->getRequestDir(),
                'TYPO3_SITE_URL' => $normalizedParams->getSiteUrl(),
                'TYPO3_SITE_SCRIPT' => $normalizedParams->getSiteScript(),
                'TYPO3_SSL' => $normalizedParams->isHttps(),
                'TYPO3_REV_PROXY' => $normalizedParams->isBehindReverseProxy(),
                'SCRIPT_NAME' => $normalizedParams->getScriptName(),
                'TYPO3_DOCUMENT_ROOT' => $normalizedParams->getDocumentRoot(),
                'SCRIPT_FILENAME' => $normalizedParams->getScriptFilename(),
                'REMOTE_ADDR' => $normalizedParams->getRemoteAddress(),
                'REMOTE_HOST' => $normalizedParams->getRemoteHost(),
                'HTTP_USER_AGENT' => $normalizedParams->getHttpUserAgent(),
                'HTTP_ACCEPT_LANGUAGE' => $normalizedParams->getHttpAcceptLanguage(),
            ]);
        }
        return $logRecord;
    }
}
