<?php

namespace Advik\BlogApi\Controller;

use Magento\Framework\App\ActionFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\RouterInterface;
use Advik\BlogApi\Model\BlogFactory;

class Router implements RouterInterface
{
	protected $actionFactory;
	protected $blogFactory;
	protected ResponseInterface $response;

	public function __construct(
		ActionFactory $actionFactory,
		BlogFactory $blogFactory,
		ResponseInterface $response
	) {
		$this->actionFactory = $actionFactory;
		$this->blogFactory = $blogFactory;
		$this->response = $response;
	}

	/* it works when setting sortOrder 60 to etc/frontend/di.xml
	 * public function match(RequestInterface $request)
	{
		$identifier = trim($request->getPathInfo(), '/');

		if (strpos($identifier, 'blog/') === 0) { //blog is frontend routes
			$postIdentifier = str_replace('blog/', '', $identifier);

			$blog = $this->blogFactory->create()->load($postIdentifier, 'identifier');
			if (!$blog->getId()) {
				return false;
			}

			$request->setModuleName('blog')
				->setControllerName('post')
				->setActionName('view')
				->setParam('id', $blog->getId());

			return $this->actionFactory->create(\Magento\Framework\App\Action\Forward::class);
		}
		return false;

	}*/

	public function match(RequestInterface $request)
	{
		// Prevent matching forwarded requests
		if ($request->getModuleName() === 'blog') {
			return null;
		}

		$identifier = trim($request->getPathInfo(), '/');

		if (preg_match('#^blog/([^/]+)$#', $identifier, $matches)) { //blog is frontend routes
			$postIdentifier = $matches[1];

			$blog = $this->blogFactory->create()->load($postIdentifier, 'identifier');
			if (!$blog->getId()) {
				return false;
			}

			$request->setModuleName('blog')
				->setControllerName('post')
				->setActionName('view')
				->setParam('id', $blog->getId());

			// Avoid rematching
			$request->setAlias(\Magento\Framework\Url::REWRITE_REQUEST_PATH_ALIAS, $identifier);

			return $this->actionFactory->create(
				\Magento\Framework\App\Action\Forward::class
			);
		}

		return null;
	}

}
