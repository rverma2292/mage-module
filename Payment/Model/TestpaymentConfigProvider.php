<?php

namespace Advik\Payment\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\View\Asset\Source;
use Magento\Payment\Model\CcConfig;

class TestpaymentConfigProvider implements ConfigProviderInterface
{
	/**
	 * @param CcConfig $ccConfig
	 * @param Source $assetSource
	 */
	public function __construct(
		public \Magento\Payment\Model\CcConfig $ccConfig,
		public Source $assetSource
	) {
		$this->ccConfig = $ccConfig;
		$this->assetSource = $assetSource;
	}

	/**
	 * @var string[]
	 */
	protected $_methodCode = 'simple';

	/**
	 * {@inheritdoc}
	 */
	public function getConfig()
	{
		return [
			'payment' => [
				'simple' => [
					'availableTypes' => [$this->_methodCode => $this->ccConfig->getCcAvailableTypes()],
					'months' => [$this->_methodCode => $this->ccConfig->getCcMonths()],
					'years' => [$this->_methodCode => $this->ccConfig->getCcYears()],
					'hasVerification' => [$this->_methodCode => $this->ccConfig->hasVerification()],
				]
			]
		];
	}
}