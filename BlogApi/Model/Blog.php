<?php
namespace Advik\BlogApi\Model;

use Advik\BlogApi\Api\Data\BlogInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\AbstractModel;

class Blog extends AbstractModel implements BlogInterface
{
	const CACHE_TAG = 'custom_blog';
	const STATUS_ENABLED = 1;
	const STATUS_DISABLED = 0;

	protected $_cacheTag = 'custom_blog';
	protected $_eventPrefix = 'custom_blog';

	protected $urlRewriteFactory;
	protected $urlRewriteResource;

	public function __construct(
		\Magento\Framework\Model\Context $context,
		\Magento\Framework\Registry $registry,
		\Magento\UrlRewrite\Model\UrlRewriteFactory $urlRewriteFactory,
		\Magento\UrlRewrite\Model\ResourceModel\UrlRewrite $urlRewriteResource,
		\Advik\BlogApi\Model\ResourceModel\Blog $resource = null,
		\Advik\BlogApi\Model\ResourceModel\Blog\Collection $resourceCollection = null,
		array $data = []
	) {
		$this->urlRewriteFactory = $urlRewriteFactory;
		$this->urlRewriteResource = $urlRewriteResource;
		parent::__construct($context, $registry, $resource, $resourceCollection, $data);
	}

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

	/**
	 * Normalize (slugify) and validate identifier before save
	 *
	 * @return $this
	 * @throws LocalizedException
	 */
	public function beforeSave()
	{
		parent::beforeSave();

		$identifier = $this->getData('identifier');
		$title = $this->getData('title');

		// If identifier is empty, auto-generate from title
		if (!$identifier && $title) {
			$identifier = $this->slugify($title);
			$this->setData('identifier', $identifier);
		} elseif ($identifier) {
			// normalize provided identifier
			$identifier = $this->slugify($identifier);
			$this->setData('identifier', $identifier);
		}

		// Check format
		if ($identifier) {
			if (strlen($identifier) > 255) {
				throw new LocalizedException(__('Identifier must be less than 256 characters.'));
			}
			if (!preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $identifier)) {
				throw new LocalizedException(
					__('Identifier contains invalid characters. Use lower-case letters, numbers and hyphens only.')
				);
			}
		} else {
			throw new LocalizedException(__('Identifier is required.'));
		}

		// Check uniqueness in custom_blog table (exclude own id)
		$exists = $this->getResource()->checkIdentifierExists($identifier, $this->getId());
		if ($exists) {
			throw new LocalizedException(__('Identifier "%1" is already used by another blog post.', $identifier));
		}

		// Check url_rewrite table for request_path conflict (store-specific)
		$storeId = 1;
		if ($this->hasData('store_id')) {
			$storeId = (int)$this->getData('store_id');
		}
		// Use resource model helper to check url_rewrite conflicts (we will implement it)
		$urlConflict = $this->getResource()->checkUrlRewriteConflict($identifier, $storeId, $this->getId());
		if ($urlConflict) {
			throw new LocalizedException(
				__('The URL key "%1" is used by other URL rewrite or entity. Choose another.', $identifier)
			);
		}

		$resource = $this->urlRewriteResource;

		$connection = $resource->getConnection();
		$table = $resource->getMainTable();
		$existingPostId = $this->getId();
		$select = $connection->select()
			->from($table, ['url_rewrite_id', 'request_path', 'target_path'])
			->where('store_id = ?', $storeId)
			->where('request_path = ?', $identifier);
		if ($existingPostId) {
			$select->where('target_path != ?', 'blog/post/view/id/' . $existingPostId);

		}


		$existing = $connection->fetchRow($select);

		if ($existing) {
			// If it's the same post, allow update
			if (!isset($postId) || $existing['target_path'] !== 'blog/post/view/id/' . $postId) {
				throw new \Magento\Framework\Exception\LocalizedException(
					__('The identifier "%1" is already used by another entity.', $identifier)
				);
			}
		}

		return $this;
	}

	/**
	 * Convert a string to slug
	 *
	 * @param string $text
	 * @return string
	 */
	protected function slugify($text)
	{
		// Lowercase
		$text = mb_strtolower((string)$text, 'UTF-8');
		// Replace non-alnum with hyphen
		$text = preg_replace('/[^a-z0-9]+/u', '-', $text);
		// Trim extra hyphens
		$text = trim($text, '-');
		// Collapse multiple hyphens
		$text = preg_replace('/-+/', '-', $text);
		return $text;
	}


	public function afterSave()
	{
		parent::afterSave();

		$identifier = $this->getIdentifier();
		$postId = $this->getId();
		$storeId = 1; // or use $this->getStoreId() if you have per-store logic
		$targetPath = 'blog/post/view/id/' . $postId;
		$idPath = 'blog/post/' . $postId;

		/** @var \Magento\UrlRewrite\Model\ResourceModel\UrlRewrite $urlRewriteResource */
		$resource = $this->urlRewriteResource;

		/** @var \Magento\UrlRewrite\Model\UrlRewriteFactory $rewriteFactory */
		$factory = $this->urlRewriteFactory;

		// Check if a URL rewrite already exists for this target path
		$connection = $resource->getConnection();
		$table = $resource->getMainTable();

		$select = $connection->select()
			->from($table)
			->where('store_id = ?', $storeId)
			->where('target_path = ?', $targetPath);

		$existing = $connection->fetchRow($select);

		if ($existing) {
			// If the request_path changed, update it
			if ($existing['request_path'] !== $identifier) {
				$rewrite = $factory->create();
				$rewrite->load($existing['url_rewrite_id']);
				$rewrite->setRequestPath($identifier)
					->setIsAutogenerated(true)
					->save();
			}
			// Else do nothing, rewrite is correct
		} else {
			// Create new URL rewrite
			$rewrite = $factory->create();
			$rewrite->setStoreId($storeId)
				->setIsAutogenerated(true)
				->setIdPath($idPath)
				->setRequestPath($identifier)
				->setTargetPath($targetPath)
				->save();
		}

		return $this;
	}

}