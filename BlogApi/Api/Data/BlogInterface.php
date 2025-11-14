<?php
namespace Advik\BlogApi\Api\Data;

interface BlogInterface
{
	/**#@+
	 * Constants for keys of data array
	 */
	const POST_ID           = 'post_id';
	const TITLE             = 'title';
	const PAGE_LAYOUT       = 'page_layout';
	const IS_ACTIVE         = 'is_active';
	const META_TITLE        = 'meta_title';
	const META_KEYWORDS     = 'meta_keywords';
	const META_DESCRIPTION  = 'meta_description';
	const IDENTIFIER        = 'identifier';
	const CONTENT_HEADING   = 'content_heading';
	const CONTENT           = 'content';
	const CREATED_AT        = 'created_at';
	const UPDATED_AT        = 'updated_at';
	/**#@-*/

	/**
	 * Get Post ID
	 * @return int|null
	 */
	public function getPostId();

	/**
	 * Set Post ID
	 * @param int $id
	 * @return $this
	 */
	public function setPostId($id);

	/**
	 * Get Title
	 * @return string|null
	 */
	public function getTitle();

	/**
	 * Set Title
	 * @param string $title
	 * @return $this
	 */
	public function setTitle($title);

	/**
	 * Get Page Layout
	 * @return string|null
	 */
	public function getPageLayout();

	/**
	 * Set Page Layout
	 * @param string|null $pageLayout
	 * @return $this
	 */
	public function setPageLayout($pageLayout);

	/**
	 * Get Is Active
	 * @return int
	 */
	public function getIsActive();

	/**
	 * Set Is Active
	 * @param int $isActive
	 * @return $this
	 */
	public function setIsActive($isActive);

	/**
	 * Get Meta Title
	 * @return string|null
	 */
	public function getMetaTitle();

	/**
	 * Set Meta Title
	 * @param string|null $metaTitle
	 * @return $this
	 */
	public function setMetaTitle($metaTitle);

	/**
	 * Get Meta Keywords
	 * @return string|null
	 */
	public function getMetaKeywords();

	/**
	 * Set Meta Keywords
	 * @param string|null $metaKeywords
	 * @return $this
	 */
	public function setMetaKeywords($metaKeywords);

	/**
	 * Get Meta Description
	 * @return string|null
	 */
	public function getMetaDescription();

	/**
	 * Set Meta Description
	 * @param string|null $metaDescription
	 * @return $this
	 */
	public function setMetaDescription($metaDescription);

	/**
	 * Get Identifier
	 * @return string
	 */
	public function getIdentifier();

	/**
	 * Set Identifier
	 * @param string $identifier
	 * @return $this
	 */
	public function setIdentifier($identifier);

	/**
	 * Get Content Heading
	 * @return string|null
	 */
	public function getContentHeading();

	/**
	 * Set Content Heading
	 * @param string|null $contentHeading
	 * @return $this
	 */
	public function setContentHeading($contentHeading);

	/**
	 * Get Content
	 * @return string|null
	 */
	public function getContent();

	/**
	 * Set Content
	 * @param string|null $content
	 * @return $this
	 */
	public function setContent($content);

	/**
	 * Get Created At
	 * @return string|null
	 */
	public function getCreatedAt();

	/**
	 * Set Created At
	 * @param string $createdAt
	 * @return $this
	 */
	public function setCreatedAt($createdAt);

	/**
	 * Get Updated At
	 * @return string|null
	 */
	public function getUpdatedAt();

	/**
	 * Set Updated At
	 * @param string $updatedAt
	 * @return $this
	 */
	public function setUpdatedAt($updatedAt);
}
