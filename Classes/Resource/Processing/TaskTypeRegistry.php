<?php

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

namespace TYPO3\CMS\Core\Resource\Processing;

use TYPO3\CMS\Core\Resource\ProcessedFile;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * The registry for task types.
 */
class TaskTypeRegistry implements SingletonInterface
{
    protected array $registeredTaskTypes = [];

    /**
     * Register task types from configuration
     */
    public function __construct()
    {
        $this->registeredTaskTypes = $GLOBALS['TYPO3_CONF_VARS']['SYS']['fal']['processingTaskTypes'];
    }

    /**
     * Returns the class that implements the given task type.
     */
    protected function getClassForTaskType(string $taskType): ?string
    {
        return $this->registeredTaskTypes[$taskType] ?? null;
    }

    /**
     * @throws \RuntimeException
     */
    public function getTaskForType(string $taskType, ProcessedFile $processedFile, array $processingConfiguration): TaskInterface
    {
        $taskClass = $this->getClassForTaskType($taskType);
        if ($taskClass === null) {
            throw new \RuntimeException('Unknown processing task "' . $taskType . '"', 1476049767);
        }

        return GeneralUtility::makeInstance($taskClass, $processedFile, $processingConfiguration);
    }
}
