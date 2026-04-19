<?php
namespace Advik\Payment\Model\Payment;

use Magento\Directory\Helper\Data as DirectoryHelper;
use Magento\Payment\Model\Method\AbstractMethod;
use Magento\Framework\Exception\LocalizedException;

class Simple extends AbstractMethod
{
	protected $_code = 'simple';
	protected $_isOffline = false;
	protected $_canAuthorize = true;

	protected $_isGateway = true;
	protected $_canCapture = true;
	protected $_canCapturePartial = true;
	protected $_canRefund = true;
	protected $_canRefundInvoicePartial = true;
	protected $_stripeApi = false;
	protected $_countryFactory;
	protected $_minAmount = null;
	protected $_maxAmount = null;
	protected $_supportedCurrencyCodes = ['USD'];
	protected $_debugReplacePrivateDataKeys = ['number', 'exp_month', 'exp_year', 'cvc'];

	public function __construct(
		\Magento\Framework\Model\Context $context,
		\Magento\Framework\Registry $registry,
		\Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
		\Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
		\Magento\Payment\Helper\Data $paymentData,
		\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
		\Magento\Payment\Model\Method\Logger $logger,
		\Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
		\Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
		array $data = [],
		DirectoryHelper $directory = null)
	{
		parent::__construct($context, $registry, $extensionFactory, $customAttributeFactory, $paymentData, $scopeConfig, $logger, $resource, $resourceCollection, $data, $directory);
	}

	/**
	 * Authorize payment abstract method
	 *
	 * @param \Magento\Payment\Model\InfoInterface $payment
	 * @param float $amount
	 * @return $this
	 * @throws LocalizedException
	 * @api
	 */
	public function authorize(\Magento\Payment\Model\InfoInterface $payment, $amount)
	{
		$ccNumber = $payment->getAdditionalInformation('cc_number');
		$ccCvv = $payment->getAdditionalInformation('cc_cid');
		$msg = "CC Number: ".$ccNumber." CVV: ".$ccCvv . " Authorize called\n";
		file_put_contents(BP . '/var/log/test_auth.log', $msg, FILE_APPEND);
		// Dummy condition
		if ($ccNumber === '4111111111111111' && $ccCvv === '123') {
			// success
			$transactionId = 'DUMMY-' . rand(100000,999999);

			$payment->setTransactionId($transactionId);
			$payment->setIsTransactionClosed(false);

			return $this;
		}
		throw new \Magento\Framework\Exception\LocalizedException(
			__('Payment Declined: Invalid dummy card.')
		);
//		if (!$this->canAuthorize()) {
//			throw new LocalizedException(__('The authorize action is not available.'));
//		}
//		return $this;
	}

	/**
	 * Capture payment abstract method
	 *
	 * @param \Magento\Payment\Model\InfoInterface $payment
	 * @param float $amount
	 * @return $this
	 * @throws LocalizedException
	 * @api
	 */
	public function capture(\Magento\Payment\Model\InfoInterface $payment, $amount)
	{
		if (!$this->canCapture()) {
			throw new LocalizedException(__('The capture action is not available.'));
		}
		return $this;
	}

	/**
	 * Refund specified amount for payment
	 *
	 * @param \Magento\Payment\Model\InfoInterface $payment
	 * @param float $amount
	 * @return $this
	 * @throws LocalizedException
	 * @api
	 */
	public function refund(\Magento\Payment\Model\InfoInterface $payment, $amount)
	{
		if (!$this->canRefund()) {
			throw new LocalizedException(__('The refund action is not available.'));
		}
		return $this;
	}

	public function assignData(\Magento\Framework\DataObject $data){
		parent::assignData($data);
		$additionalData = $data->getData('additional_data');
		$ccNumber = $additionalData['cc_number'] ?? null;
		$ccCvv = $additionalData['cc_cid'] ?? null;

		$this->getInfoInstance()->setAdditionalInformation('cc_number', $ccNumber);
		$this->getInfoInstance()->setAdditionalInformation('cc_cid', $ccCvv);
		return $this;
	}
}

