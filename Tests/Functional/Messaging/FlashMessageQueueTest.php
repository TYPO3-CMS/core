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

namespace TYPO3\CMS\Core\Tests\Functional\Messaging;

use PHPUnit\Framework\Attributes\Test;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageQueue;
use TYPO3\CMS\Core\Type\ContextualFeedbackSeverity;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

final class FlashMessageQueueTest extends FunctionalTestCase
{
    #[Test]
    public function getAllMessagesContainsEnqueuedMessage(): void
    {
        $this->importCSVDataSet(__DIR__ . '/../Fixtures/be_users.csv');
        $this->setUpBackendUser(1);
        $flashMessage = new FlashMessage('Foo', 'Bar', ContextualFeedbackSeverity::OK, true);
        $flashMessageQueue = new FlashMessageQueue('core.template.flashMessages');
        $flashMessageQueue->addMessage($flashMessage);
        self::assertEquals([$flashMessage], $flashMessageQueue->getAllMessages());
    }

    #[Test]
    public function messagesCanBeFilteredBySeverity(): void
    {
        $this->importCSVDataSet(__DIR__ . '/../Fixtures/be_users.csv');
        $this->setUpBackendUser(1);
        $flashMessageQueue = new FlashMessageQueue('core.template.flashMessages');
        $messages = [
            0 => new FlashMessage('This is a test message', '1', ContextualFeedbackSeverity::NOTICE),
            1 => new FlashMessage('This is another test message', '2', ContextualFeedbackSeverity::WARNING),
        ];
        $flashMessageQueue->enqueue($messages[0]);
        $flashMessageQueue->enqueue($messages[1]);

        $filteredFlashMessages = $flashMessageQueue->getAllMessages(ContextualFeedbackSeverity::NOTICE);

        self::assertCount(1, $filteredFlashMessages);

        reset($filteredFlashMessages);
        $flashMessage = current($filteredFlashMessages);
        self::assertEquals($messages[0], $flashMessage);
    }

    #[Test]
    public function getAllMessagesAndFlushContainsEnqueuedMessage(): void
    {
        $this->importCSVDataSet(__DIR__ . '/../Fixtures/be_users.csv');
        $this->setUpBackendUser(1);
        $flashMessage = new FlashMessage('Foo', 'Bar', ContextualFeedbackSeverity::OK, true);
        $flashMessageQueue = new FlashMessageQueue('core.template.flashMessages');
        $flashMessageQueue->addMessage($flashMessage);
        self::assertEquals([$flashMessage], $flashMessageQueue->getAllMessagesAndFlush());
    }

    #[Test]
    public function getAllMessagesAndFlushClearsSessionStack(): void
    {
        $this->importCSVDataSet(__DIR__ . '/../Fixtures/be_users.csv');
        $this->setUpBackendUser(1);
        $flashMessage = new FlashMessage('Foo', 'Bar', ContextualFeedbackSeverity::OK, true);
        $flashMessageQueue = new FlashMessageQueue('core.template.flashMessages');
        $flashMessageQueue->addMessage($flashMessage);
        $flashMessageQueue->getAllMessagesAndFlush();
        self::assertEquals([], $flashMessageQueue->getAllMessagesAndFlush());
    }

    #[Test]
    public function getMessagesAndFlushCanFilterBySeverity(): void
    {
        $this->importCSVDataSet(__DIR__ . '/../Fixtures/be_users.csv');
        $this->setUpBackendUser(1);
        $flashMessageQueue = new FlashMessageQueue('core.template.flashMessages');
        $messages = [
            0 => new FlashMessage('This is a test message', '1', ContextualFeedbackSeverity::NOTICE),
            1 => new FlashMessage('This is another test message', '2', ContextualFeedbackSeverity::WARNING),
        ];
        $flashMessageQueue->addMessage($messages[0]);
        $flashMessageQueue->addMessage($messages[1]);

        $filteredFlashMessages = $flashMessageQueue->getAllMessagesAndFlush(ContextualFeedbackSeverity::NOTICE);

        self::assertCount(1, $filteredFlashMessages);

        reset($filteredFlashMessages);
        $flashMessage = current($filteredFlashMessages);
        self::assertEquals($messages[0], $flashMessage);

        self::assertEquals([], $flashMessageQueue->getAllMessages(ContextualFeedbackSeverity::NOTICE));
        self::assertEquals([$messages[1]], array_values($flashMessageQueue->getAllMessages()));
    }

    #[Test]
    public function getAllMessagesReturnsSessionFlashMessageAndTransientFlashMessage(): void
    {
        $this->importCSVDataSet(__DIR__ . '/../Fixtures/be_users.csv');
        $this->setUpBackendUser(1);
        $flashMessageQueue = new FlashMessageQueue('core.template.flashMessages');
        $flashMessage1 = new FlashMessage('Transient', 'Title', ContextualFeedbackSeverity::OK, false);
        $flashMessage2 = new FlashMessage('Session', 'Title', ContextualFeedbackSeverity::OK, true);
        $flashMessageQueue->addMessage($flashMessage1);
        $flashMessageQueue->addMessage($flashMessage2);

        self::assertCount(2, $flashMessageQueue->getAllMessages());
    }

    #[Test]
    public function clearClearsTheQueue(): void
    {
        $this->importCSVDataSet(__DIR__ . '/../Fixtures/be_users.csv');
        $this->setUpBackendUser(1);
        $flashMessage = new FlashMessage('Foo', 'Bar', ContextualFeedbackSeverity::OK, true);
        $flashMessageQueue = new FlashMessageQueue('core.template.flashMessages');
        $flashMessageQueue->addMessage($flashMessage);
        $flashMessageQueue->clear();
        self::assertCount(0, $flashMessageQueue);
    }

    #[Test]
    public function toArrayOnlyRespectsTransientFlashMessages(): void
    {
        $this->importCSVDataSet(__DIR__ . '/../Fixtures/be_users.csv');
        $this->setUpBackendUser(1);
        $flashMessageQueue = new FlashMessageQueue('core.template.flashMessages');
        $flashMessage1 = new FlashMessage('Transient', 'Title', ContextualFeedbackSeverity::OK, false);
        $flashMessage2 = new FlashMessage('Session', 'Title', ContextualFeedbackSeverity::OK, true);
        $flashMessageQueue->addMessage($flashMessage1);
        $flashMessageQueue->addMessage($flashMessage2);

        self::assertCount(1, $flashMessageQueue);
    }
}
