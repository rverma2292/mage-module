<?php

namespace Advik\BlogApi\Block\Post;

use Magento\Framework\View\Element\Template;

class View extends Template
{
	public function getPost()
	{
		return $this->getData('post');
	}
}
