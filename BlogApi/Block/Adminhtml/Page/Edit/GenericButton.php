<?php

namespace Advik\BlogApi\Block\Adminhtml\Page\Edit;

use Magento\Backend\Block\Widget\Context;
use Magento\Framework\Exception\NoSuchEntityException;
use Advik\BlogApi\Model\BlogFactory;

/**
 * Class GenericButton
 */
class GenericButton
{
    /**
     * @var Context
     */
    protected $context;

	/**
	 * @var BlogFactory
	 */
	protected BlogFactory $blogFactory;

	/**
	 * @param Context $context
	 * @param BlogFactory $blogFactory
	 */
    public function __construct(
        Context $context,
        BlogFactory $blogFactory
    ) {
        $this->context = $context;
        $this->blogFactory = $blogFactory;
    }

    /**
     * Return CMS page ID
     *
     * @return int|null
     */
    public function getPostId()
    {
	    $blogId = $this->context->getRequest()->getParam('id');
	    if (!$blogId) {
		    return null;
	    }

	    $blog = $this->blogFactory->create()->load($blogId);
	    if ($blog->getPostId()) {
		    return $blog->getPostId(); // or ->getId() if that's the PK
	    }

	    return null;
    }

    /**
     * Generate url by route and parameters
     *
     * @param   string $route
     * @param   array $params
     * @return  string
     */
    public function getUrl($route = '', $params = [])
    {
        return $this->context->getUrlBuilder()->getUrl($route, $params);
    }
}
