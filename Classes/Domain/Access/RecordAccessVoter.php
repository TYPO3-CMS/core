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

namespace TYPO3\CMS\Core\Domain\Access;

use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Schema\Capability\TcaSchemaCapability;
use TYPO3\CMS\Core\Schema\TcaSchemaFactory;

/**
 * Checks if a record can be accessed (usually in TYPO3 Frontend) due to various "enableFields" or group access checks.
 *
 * Not related to "write permissions" etc.
 */
#[Autoconfigure(public: true)]
class RecordAccessVoter
{
    public function __construct(
        protected readonly EventDispatcherInterface $eventDispatcher,
        protected readonly TcaSchemaFactory $tcaSchemaFactory,
    ) {}

    /**
     * Checks page record for enableFields
     * Returns TRUE if enableFields does not disable the page record.
     * Takes notice of the includeHiddenPages visibility aspect flag and uses SIM_ACCESS_TIME for start/endtime evaluation
     *
     * @param string $table the TCA table to check for
     * @param array $record The record to evaluate (needs fields: hidden, starttime, endtime, fe_group)
     * @param Context $context Context API to check against
     * @return bool TRUE, if record is viewable.
     */
    public function accessGranted(string $table, array $record, Context $context): bool
    {
        $event = new RecordAccessGrantedEvent($table, $record, $context);
        $this->eventDispatcher->dispatch($event);
        if ($event->isPropagationStopped()) {
            return $event->accessGranted();
        }
        $record = $event->getRecord();

        $schema = $this->tcaSchemaFactory->get($table);
        $visibilityAspect = $context->getAspect('visibility');
        $includeHidden = $table === 'pages'
            ? $visibilityAspect->includeHiddenPages()
            : $visibilityAspect->includeHiddenContent();

        // Hidden field is active and hidden records should not be included
        if ($schema->hasCapability(TcaSchemaCapability::RestrictionDisabledField)) {
            $fieldName = $schema->getCapability(TcaSchemaCapability::RestrictionDisabledField)->getFieldName();
            if (($record[$fieldName] ?? false) && !$includeHidden) {
                return false;
            }
        }
        // Records' starttime set AND is HIGHER than the current access time
        if ($schema->hasCapability(TcaSchemaCapability::RestrictionStartTime)) {
            $fieldName = $schema->getCapability(TcaSchemaCapability::RestrictionStartTime)->getFieldName();
            if (isset($record[$fieldName]) && (int)$record[$fieldName] > $GLOBALS['SIM_ACCESS_TIME']) {
                return false;
            }
        }
        // Records' endtime is set AND NOT "0" AND LOWER than the current access time
        if ($schema->hasCapability(TcaSchemaCapability::RestrictionEndTime)) {
            $fieldName = $schema->getCapability(TcaSchemaCapability::RestrictionEndTime)->getFieldName();
            if (isset($record[$fieldName])
                && ((int)$record[$fieldName] !== 0)
                && ((int)$record[$fieldName] < $GLOBALS['SIM_ACCESS_TIME'])
            ) {
                return false;
            }
        }
        // Insufficient group access
        if ($this->groupAccessGranted($table, $record, $context) === false) {
            return false;
        }
        // Record is available
        return true;
    }

    /**
     * Check group access against a record, if the current users' groups match the fe_group values of the record.
     *
     * @param string $table the TCA table to check for
     * @param array $record The record to evaluate (needs enableField: fe_group)
     * @param Context $context Context API to check against
     * @return bool TRUE, if group access is granted.
     */
    public function groupAccessGranted(string $table, array $record, Context $context): bool
    {
        if (!$this->tcaSchemaFactory->has($table)) {
            return true;
        }
        $schema = $this->tcaSchemaFactory->get($table);
        // No fe_group field in TCA, so no group access check
        if (!$schema->hasCapability(TcaSchemaCapability::RestrictionUserGroup)) {
            return true;
        }
        $fieldName = $schema->getCapability(TcaSchemaCapability::RestrictionUserGroup)->getFieldName();
        // Field not given, so no group access check
        if (!($record[$fieldName] ?? false)) {
            return true;
        }
        // No frontend user, but 'fe_group' is not empty, so shut this down.
        if (!$context->hasAspect('frontend.user')) {
            return false;
        }
        $pageGroupList = explode(',', (string)$record[$fieldName]);
        return count(array_intersect($context->getAspect('frontend.user')->getGroupIds(), $pageGroupList)) > 0;
    }

    /**
     * Checks if the current page of the root line is visible.
     *
     * If the field extendToSubpages is 0, access is granted,
     * else the fields hidden, starttime, endtime, fe_group are evaluated.
     *
     * @internal this is a special use case and should only be used with care, not part of TYPO3's Public API.
     */
    public function accessGrantedForPageInRootLine(array $pageRecord, Context $context): bool
    {
        return !($pageRecord['extendToSubpages'] ?? false) || $this->accessGranted('pages', $pageRecord, $context);
    }
}
