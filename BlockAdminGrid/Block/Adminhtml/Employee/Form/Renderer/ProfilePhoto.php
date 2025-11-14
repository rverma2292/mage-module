<?php
namespace Advik\BlockAdminGrid\Block\Adminhtml\Employee\Form\Renderer;

use Magento\Framework\Data\Form\Element\Renderer\RendererInterface;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;

class ProfilePhoto extends Template implements RendererInterface
{
	protected $_template = 'Advik_BlockAdminGrid::employee/form/profile_photo.phtml';

	protected $_coreRegistry;

	public function __construct(
		Template\Context $context,
		Registry $coreRegistry,
		array $data = []
	)
	{
		$this->_coreRegistry = $coreRegistry;
		parent::__construct($context, $data);
	}

	public function render(AbstractElement $element)
	{
		$this->setElement($element);
		return $this->toHtml();
	}

	public function getModel()
	{
		return $this->_coreRegistry->registry('blockadmingrid_employee');
	}

	public function getMediaUrl($path)
	{
		return $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA)
			. 'employee/' . ltrim($path, '/');
	}
}
