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

namespace TYPO3\CMS\Core\Tests\Unit\Messaging;

use PHPUnit\Framework\Attributes\Test;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Type\ContextualFeedbackSeverity;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

final class FlashMessageTest extends UnitTestCase
{
    #[Test]
    public function jsonSerializeReturnsArrayWithDetailData(): void
    {
        $message = new FlashMessage('aMessage', 'aTitle', ContextualFeedbackSeverity::INFO);
        $expected = [
            'severity' => ContextualFeedbackSeverity::INFO->value,
            'title' => 'aTitle',
            'message' => 'aMessage',
            'storeInSession' => false,
        ];
        self::assertEquals($expected, $message->jsonSerialize());
    }
}
