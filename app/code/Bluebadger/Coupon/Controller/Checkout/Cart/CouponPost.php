<?php

namespace Bluebadger\Coupon\Controller\Checkout\Cart;

use Magento\Framework\Exception\LocalizedException;

/**
 * Class CouponPost
 * @package Bluebadger\Coupon\Controller\Checkout\Cart
 */
class CouponPost extends \Magento\Checkout\Controller\Cart\CouponPost
{
    const KEY_COUPON = 'coupon';

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var \Bluebadger\Coupon\Helper\Config
     */
    protected $config;

    /**
     * @var \Bluebadger\Coupon\Model\EmailManagement
     */
    protected $emailManagement;

    /**
     * CouponPost constructor.
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator
     * @param \Magento\Checkout\Model\Cart $cart
     * @param \Magento\SalesRule\Model\CouponFactory $couponFactory
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Bluebadger\Coupon\Helper\Config $config
     * @param \Bluebadger\Coupon\Model\EmailManagement $emailManagement
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
        \Magento\Checkout\Model\Cart $cart,
        \Magento\SalesRule\Model\CouponFactory $couponFactory,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Psr\Log\LoggerInterface $logger,
        \Bluebadger\Coupon\Helper\Config $config,
        \Bluebadger\Coupon\Model\EmailManagement $emailManagement
    )
    {
        parent::__construct($context, $scopeConfig, $checkoutSession, $storeManager, $formKeyValidator, $cart, $couponFactory, $quoteRepository);
        $this->logger = $logger;
        $this->config = $config;
        $this->emailManagement = $emailManagement;
    }

    /**
     * Initialize coupon
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
        $couponCode = $this->getRequest()->getParam('remove') == 1
            ? ''
            : trim($this->getRequest()->getParam('coupon_code'));

        $cartQuote = $this->cart->getQuote();
        $oldCouponCode = $cartQuote->getCouponCode();

        $codeLength = strlen($couponCode);
        if (!$codeLength && !strlen($oldCouponCode)) {
            return $this->_goBack();
        }

        try {
            $isCodeLengthValid = $codeLength && $codeLength <= \Magento\Checkout\Helper\Cart::COUPON_CODE_MAX_LENGTH;

            if ($this->config->isEnabled()) {
                $ruleCouponCode = $this->config->getCouponCode();

                if ($couponCode == $ruleCouponCode) {
                    $email = $this->_checkoutSession->getQuote()->getCustomerEmail();

                    if (empty($email)) {
                        throw new LocalizedException(__('An email address must be set.'));
                    }

                    if (!$this->emailManagement->isEmailValid($email)) {
                        throw new LocalizedException(__('Invalid email address.'));
                    }
                }
            }

            $itemsCount = $cartQuote->getItemsCount();
            if ($itemsCount) {
                $cartQuote->getShippingAddress()->setCollectShippingRates(true);
                $cartQuote->setCouponCode($isCodeLengthValid ? $couponCode : '')->collectTotals();
                $this->quoteRepository->save($cartQuote);
            }

            if ($codeLength) {
                $escaper = $this->_objectManager->get('Magento\Framework\Escaper');
                if (!$itemsCount) {
                    if ($isCodeLengthValid) {
                        $coupon = $this->couponFactory->create();
                        $coupon->load($couponCode, 'code');
                        if ($coupon->getId()) {
                            $this->_checkoutSession->getQuote()->setCouponCode($couponCode)->save();
                            $this->messageManager->addSuccess(
                                __(
                                    'You used coupon code "%1".',
                                    $escaper->escapeHtml($couponCode)
                                )
                            );
                        } else {
                            $this->messageManager->addError(
                                __(
                                    'The coupon code "%1" is not valid.',
                                    $escaper->escapeHtml($couponCode)
                                )
                            );
                        }
                    } else {
                        $this->messageManager->addError(
                            __(
                                'The coupon code "%1" is not valid.',
                                $escaper->escapeHtml($couponCode)
                            )
                        );
                    }
                } else {
                    if ($isCodeLengthValid && $couponCode == $cartQuote->getCouponCode()) {
                        $this->messageManager->addSuccess(
                            __(
                                'You used coupon code "%1".',
                                $escaper->escapeHtml($couponCode)
                            )
                        );
                    } else {
                        $this->messageManager->addError(
                            __(
                                'The coupon code "%1" is not valid.',
                                $escaper->escapeHtml($couponCode)
                            )
                        );
                        $this->cart->save();
                    }
                }
            } else {
                $this->messageManager->addSuccess(__('You canceled the coupon code.'));
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addError(__('We cannot apply the coupon code.'));
            $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
        }

        return $this->_goBack();
    }
}