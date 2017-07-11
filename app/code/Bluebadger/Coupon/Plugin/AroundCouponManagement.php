<?php

namespace Bluebadger\Coupon\Plugin;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class AroundCouponManagement
 * @package Bluebadger\Coupon\Plugin
 */
class AroundCouponManagement
{
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
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $session;

    /**
     * AroundCouponManagement constructor.
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Bluebadger\Coupon\Helper\Config $config
     * @param \Bluebadger\Coupon\Model\EmailManagement $emailManagement
     * @param \Magento\Framework\App\Request\Http $request
     */
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Bluebadger\Coupon\Helper\Config $config,
        \Bluebadger\Coupon\Model\EmailManagement $emailManagement,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Checkout\Model\Session $session
    )
    {
        $this->logger = $logger;
        $this->config = $config;
        $this->emailManagement = $emailManagement;
        $this->request = $request;
        $this->session = $session;
    }

    /**
     * @param \Magento\Quote\Model\CouponManagement $couponManagement
     * @param callable $proceed
     * @param array ...$args
     */
    public function aroundSet(\Magento\Quote\Model\CouponManagement $couponManagement, callable $proceed, $cartId, $couponCode)
    {
        if ($this->config->isEnabled()) {
            if ($this->config->getCouponCode() == $couponCode) {
                $email = $this->session->getQuote()->getCustomerEmail();

                if (empty($email)) {
                    throw new LocalizedException(__('Not email address set.'));
                }

                if (!$this->emailManagement->isEmailValid($email)) {
                    throw new LocalizedException(__('Email address is invalid'));
                }
            }
        }

        return $proceed($cartId, $couponCode);
    }
}