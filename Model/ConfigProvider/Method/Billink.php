<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MIT License
 * It is available through the world-wide-web at this URL:
 * https://tldrlegal.com/license/mit-license
 * If you are unable to obtain it through the world-wide-web, please send an email
 * to support@buckaroo.nl so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this module to newer
 * versions in the future. If you wish to customize this module for your
 * needs please contact support@buckaroo.nl for more information.
 *
 * @copyright Copyright (c) Buckaroo B.V.
 * @license   https://tldrlegal.com/license/mit-license
 */

namespace Buckaroo\Magento2\Model\ConfigProvider\Method;

use Buckaroo\Magento2\Helper\PaymentFee;
use Buckaroo\Magento2\Model\ConfigProvider\AllowedCurrencies;
use Buckaroo\Magento2\Model\Method\Billink as BillinkMethod;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\View\Asset\Repository;
use Magento\Store\Model\ScopeInterface;


/**
 * @method getDueDate()
 * @method getSendEmail()
 */
class Billink extends AbstractConfigProvider
{
    const XPATH_ALLOWED_CURRENCIES = 'buckaroo/buckaroo_magento2_billink/allowed_currencies';

    const XPATH_ALLOW_SPECIFIC   = 'payment/buckaroo_magento2_billink/allowspecific';
    const XPATH_SPECIFIC_COUNTRY = 'payment/buckaroo_magento2_billink/specificcountry';

    const XPATH_BILLINK_ACTIVE               = 'payment/buckaroo_magento2_billink/active';
    const XPATH_BILLINK_PAYMENT_FEE          = 'payment/buckaroo_magento2_billink/payment_fee';
    const XPATH_BILLINK_PAYMENT_FEE_LABEL    = 'payment/buckaroo_magento2_billink/payment_fee_label';
    const XPATH_BILLINK_SEND_EMAIL           = 'payment/buckaroo_magento2_billink/send_email';
    const XPATH_BILLINK_ACTIVE_STATUS        = 'payment/buckaroo_magento2_billink/active_status';
    const XPATH_BILLINK_ORDER_STATUS_SUCCESS = 'payment/buckaroo_magento2_billink/order_status_success';
    const XPATH_BILLINK_ORDER_STATUS_FAILED  = 'payment/buckaroo_magento2_billink/order_status_failed';
    const XPATH_BILLINK_AVAILABLE_IN_BACKEND = 'payment/buckaroo_magento2_billink/available_in_backend';
    const XPATH_BILLINK_DUE_DATE             = 'payment/buckaroo_magento2_billink/due_date';
    const XPATH_BILLINK_ALLOWED_CURRENCIES   = 'payment/buckaroo_magento2_billink/allowed_currencies';

    protected $assetRepo;
    protected $scopeConfig;
    protected $allowedCurrencies = null;
    protected $paymentFeeHelper;
    protected $quote;

    public function __construct(
        Repository $assetRepo,
        ScopeConfigInterface $scopeConfig,
        AllowedCurrencies $allowedCurrencies,
        PaymentFee $paymentFeeHelper,
        \Magento\Checkout\Model\Session $checkoutSession
    ) {
        parent::__construct($assetRepo, $scopeConfig, $allowedCurrencies, $paymentFeeHelper);
        $this->quote = $checkoutSession->getQuote();
    }

    /**
     * @return array
     */
    public function getConfig()
    {
        if (!$this->scopeConfig->getValue(
            static::XPATH_BILLINK_ACTIVE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        )) {
            return [];
        }

        $paymentFeeLabel = $this->getBuckarooPaymentFeeLabel(BillinkMethod::PAYMENT_METHOD_CODE);

        return [
            'payment' => [
                'buckaroo' => [
                    'billink'  => [
                        'sendEmail'         => (bool) $this->getSendEmail(),
                        'paymentFeeLabel'   => $paymentFeeLabel,
                        'allowedCurrencies' => $this->getAllowedCurrencies(),
                    ],
                    'response' => [],
                ],
            ],
        ];
    }

    /**
     * @param null|int $storeId
     *
     * @return float
     */
    public function getPaymentFee($storeId = null)
    {
        $paymentFee = $this->scopeConfig->getValue(
            self::XPATH_BILLINK_PAYMENT_FEE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );

        return $paymentFee ? $paymentFee : false;
    }

    /**
     * businessMethod 1 = B2C
     * businessMethod 2 = B2B
     *
     * @return bool|int
     */
    public function getBusiness()
    {
        $business = (int) $this->scopeConfig->getValue(
            self::XPATH_BILLINK_BUSINESS,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        return $business ? $business : false;
    }

    /**
     * {@inheritdoc}
     */
    public function getActive($store = null)
    {
        return $this->scopeConfig->getValue(
            self::XPATH_BILLINK_ACTIVE,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getPaymentFeeLabel($store = null)
    {
        return $this->scopeConfig->getValue(
            self::XPATH_BILLINK_PAYMENT_FEE_LABEL,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getSendEmail($store = null)
    {
        return $this->scopeConfig->getValue(
            self::XPATH_BILLINK_SEND_EMAIL,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getActiveStatus($store = null)
    {
        return $this->scopeConfig->getValue(
            self::XPATH_BILLINK_ACTIVE_STATUS,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getOrderStatusSuccess($store = null)
    {
        return $this->scopeConfig->getValue(
            self::XPATH_BILLINK_ORDER_STATUS_SUCCESS,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getOrderStatusFailed($store = null)
    {
        return $this->scopeConfig->getValue(
            self::XPATH_BILLINK_ORDER_STATUS_FAILED,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getAvailableInBackend($store = null)
    {
        return $this->scopeConfig->getValue(
            self::XPATH_BILLINK_AVAILABLE_IN_BACKEND,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getDueDate($store = null)
    {
        return $this->scopeConfig->getValue(
            self::XPATH_BILLINK_DUE_DATE,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }
}
