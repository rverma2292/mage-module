<?php
namespace Advik\BlogApi\Model;

use Advik\BlogApi\Api\Data\BlogInterface;
use Magento\Framework\Model\AbstractModel;

class Blog extends AbstractModel implements BlogInterface
{
	const CACHE_TAG = 'custom_blog';

	protected $_cacheTag = 'custom_blog';

	protected $_eventPrefix = 'custom_blog';

	protected function _construct()
	{
		$this->_init('Advik\BlogApi\Model\ResourceModel\Blog');
	}

	public function getPostId()
	{
		return $this->getData(self::POST_ID);
	}

	public function setPostId($id)
	{
		return $this->setData(self::POST_ID, $id);
	}

	public function getTitle()
	{
		return $this->getData(self::TITLE);
	}

	public function setTitle($title)
	{
		return $this->setData(self::TITLE, $title);
	}

	public function getPageLayout()
	{
		return $this->getData(self::PAGE_LAYOUT);
	}

	public function setPageLayout($pageLayout)
	{
		return $this->setData(self::PAGE_LAYOUT, $pageLayout);
	}

	public function getIsActive()
	{
		return $this->getData(self::IS_ACTIVE);
	}

	public function setIsActive($isActive)
	{
		return $this->setData(self::IS_ACTIVE, $isActive);
	}

	public function getMetaTitle()
	{
		return $this->getData(self::META_TITLE);
	}

	public function setMetaTitle($metaTitle)
	{
		return $this->setData(self::META_TITLE, $metaTitle);
	}

	public function getMetaKeywords()
	{
		return $this->getData(self::META_KEYWORDS);
	}

	public function setMetaKeywords($metaKeywords)
	{
		return $this->setData(self::META_KEYWORDS, $metaKeywords);
	}

	public function getMetaDescription()
	{
		return $this->getData(self::META_DESCRIPTION);
	}

	public function setMetaDescription($metaDescription)
	{
		return $this->setData(self::META_DESCRIPTION, $metaDescription);
	}

	public function getIdentifier()
	{
		return $this->getData(self::IDENTIFIER);
	}

	public function setIdentifier($identifier)
	{
		return $this->setData(self::IDENTIFIER, $identifier);
	}

	public function getContentHeading()
	{
		return $this->getData(self::CONTENT_HEADING);
	}

	public function setContentHeading($contentHeading)
	{
		return $this->setData(self::CONTENT_HEADING, $contentHeading);
	}

	public function getContent()
	{
		return $this->getData(self::CONTENT);
	}

	public function setContent($content)
	{
		return $this->setData(self::CONTENT, $content);
	}

	public function getCreatedAt()
	{
		return $this->getData(self::CREATED_AT);
	}

	public function setCreatedAt($createdAt)
	{
		return $this->setData(self::CREATED_AT, $createdAt);
	}

	public function getUpdatedAt()
	{
		return $this->getData(self::UPDATED_AT);
	}

	public function setUpdatedAt($updatedAt)
	{
		return $this->setData(self::UPDATED_AT, $updatedAt);
	}

}