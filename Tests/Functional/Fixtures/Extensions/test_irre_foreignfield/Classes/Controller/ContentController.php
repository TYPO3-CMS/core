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

namespace TYPO3Tests\TestIrreForeignfield\Controller;

use TYPO3\CMS\Extbase\Annotation as Extbase;
use TYPO3\CMS\Extbase\Mvc\View\JsonView;
use TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMapFactory;
use TYPO3Tests\TestIrreForeignfield\Domain\Model\Content;
use TYPO3Tests\TestIrreForeignfield\Domain\Repository\ContentRepository;
use TYPO3Tests\TestIrreForeignfield\Service\QueueService;

/**
 * ContentController
 */
class ContentController extends AbstractController
{
    private ContentRepository $contentRepository;

    /**
     * @var string
     */
    protected $defaultViewObjectName = JsonView::class;

    public function __construct(
        DataMapFactory $dataMapFactory,
        QueueService $queueService,
        ContentRepository $contentRepository
    ) {
        parent::__construct($dataMapFactory, $queueService);
        $this->contentRepository = $contentRepository;
    }

    public function listAction()
    {
        $contents = $this->contentRepository->findAll();
        $value = $this->getStructure($contents);
        return $this->process($value);
    }

    public function showAction(Content $content)
    {
        $value = $this->getStructure($content);
        return $this->process($value);
    }

    /**
     * @Extbase\IgnoreValidation("newContent")
     */
    public function newAction(Content $newContent = null): void
    {
        $this->view->assign('newContent', $newContent);
    }

    public function createAction(Content $newContent): void
    {
        $this->contentRepository->add($newContent);
        $this->redirect('list');
    }

    /**
     * @Extbase\IgnoreValidation("content")
     */
    public function editAction(Content $content): void
    {
        $this->view->assign('content', $content);
    }

    public function updateAction(Content $content): void
    {
        $this->contentRepository->update($content);
        $this->redirect('list');
    }

    public function deleteAction(Content $content): void
    {
        $this->contentRepository->remove($content);
        $this->redirect('list');
    }
}
