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

namespace TYPO3\CMS\Core\Tests\Functional\Authentication;

use PHPUnit\Framework\Attributes\Test;
use Psr\Log\NullLogger;
use TYPO3\CMS\Core\Authentication\AuthenticationService;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * Testcase for class \TYPO3\CMS\Core\Authentication\AuthenticationService
 */
final class AuthenticationServiceTest extends FunctionalTestCase
{
    /**
     * @var AuthenticationService
     */
    protected $subject;

    protected function setUp(): void
    {
        $this->subject = new AuthenticationService();
        $this->subject->setLogger(new NullLogger());
        parent::setUp();
        $this->importCSVDataSet(__DIR__ . '/../../../../core/Tests/Functional/Fixtures/be_users.csv');
    }

    #[Test]
    public function getUserReturnsOnlyNotDeletedRecords(): void
    {
        $this->subject->pObj = new BackendUserAuthentication();
        $this->subject->login = [
            'status' => 'login',
            'uname' => 'test1',
            'uident' => 'password',
            'uident_text' => 'password',
        ];
        $this->subject->db_user = [
            'table' => 'be_users',
            'userid_column' => 'uid',
            'enable_clause' => '',
            'username_column' => 'username',
        ];
        $result = $this->subject->getUser();
        self::assertSame('test1', $result['username']);
        self::assertSame(0, (int)$result['deleted']);
    }
}
