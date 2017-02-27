<?php
/**
 * Unirgy LLC
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.unirgy.com/LICENSE-M1.txt
 *
 * @category   Unirgy
 * @package    Unirgy_Dropship
 * @copyright  Copyright (c) 2008-2009 Unirgy LLC (http://www.unirgy.com)
 * @license    http:///www.unirgy.com/LICENSE-M1.txt
 */

namespace Unirgy\DropshipSplit\Helper\ProtectedCode;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Quote\Model\Quote\Address\RateResult\MethodFactory;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Shipping\Model\Rate\ResultFactory;
use Magento\Store\Model\ScopeInterface;
use Unirgy\Dropship\Helper\Data as HelperData;
use Unirgy\Dropship\Helper\ProtectedCode as HelperProtectedCode;
use Unirgy\Dropship\Model\Source;
use Zend\Json\Json;

class Context
{
    /**
     * @var HelperData
     */
    public $_hlp;

    /**
     * @var HelperProtectedCode
     */
    public $_helperProtectedCode;

    /**
     * @var Source
     */
    public $_modelSource;

    /**
     * @var ScopeConfigInterface
     */
    public $_configScopeConfigInterface;

    /**
     * @var ResultFactory
     */
    public $_rateResultFactory;

    /**
     * @var MethodFactory
     */
    public $_rateResultMethodFactory;

    /**
     * @var ManagerInterface
     */
    public $_eventManagerInterface;

    public function __construct(
        \Unirgy\Dropship\Helper\Data $helperData,
        \Unirgy\Dropship\Helper\ProtectedCode $helperProtectedCode,
        \Unirgy\Dropship\Model\Source $modelSource,
        \Magento\Framework\App\Config\ScopeConfigInterface $configScopeConfigInterface,
        \Magento\Shipping\Model\Rate\ResultFactory $rateResultFactory,
        \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateResultMethodFactory,
        \Magento\Framework\Event\ManagerInterface $eventManagerInterface)
    {
        $this->_hlp = $helperData;
        $this->_helperProtectedCode = $helperProtectedCode;
        $this->_modelSource = $modelSource;
        $this->_configScopeConfigInterface = $configScopeConfigInterface;
        $this->_rateResultFactory = $rateResultFactory;
        $this->_rateResultMethodFactory = $rateResultMethodFactory;
        $this->_eventManagerInterface = $eventManagerInterface;
    }
}