<?php

namespace Advik\BlogApi\Controller\Post;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Advik\BlogApi\Model\BlogFactory;
use Magento\Framework\View\Result\PageFactory;

class View extends Action
{
	protected $blogFactory;
	protected PageFactory $resultPageFactory;

	public function __construct(Context $context, BlogFactory $blogFactory, PageFactory $resultPageFactory)
	{
		parent::__construct($context);
		$this->blogFactory = $blogFactory;
		$this->resultPageFactory = $resultPageFactory;
	}

	public function execute()
	{
		$id = $this->getRequest()->getParam('id');
		$blog = $this->blogFactory->create()->load($id);

		if (!$blog->getId()) {
			return $this->_forward('noroute');
		}

		$resultPage = $this->resultPageFactory->create();
		$resultPage->getConfig()->getTitle()->set($blog->getTitle());
		$resultPage->getConfig()->getTitle()->set($blog->getMetaTitle() ?: $blog->getTitle());
		$resultPage->getConfig()->setDescription($blog->getMetaDescription() ?: substr(strip_tags($blog->getContent()), 0, 160));
		$resultPage->getConfig()->setKeywords($blog->getMetaKeywords());

		// Canonical URL
		$canonicalUrl = $this->_url->getUrl('blog/' . $blog->getIdentifier());
		$resultPage->getConfig()->addRemotePageAsset($canonicalUrl, 'canonical', ['attributes' => ['rel' => 'canonical']]);
		$block = $resultPage->getLayout()->getBlock('blog.post');

		if ($block) {
			$block->setData('post', $blog);
		}
		return $resultPage;
	}
}
