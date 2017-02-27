<?php
/**
 * Magestore
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magestore.com license that is
 * available through the world-wide-web at this URL:
 * http://www.magestore.com/license-agreement.html
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Magestore
 * @package     Magestore_Giftvoucher
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */
namespace Magestore\Giftvoucher\Controller\Index;

use Magento\Framework\Controller\ResultFactory;
use Magento\Customer\Model\Session;

/**
 * Giftvoucher Index Index Action
 *
 * @category Magestore
 * @package  Magestore_Giftvoucher
 * @module   Giftvoucher
 * @author   Magestore Developer
 */
class Index extends \Magestore\Giftvoucher\Controller\Action
{
    public function execute()
    {
     if ($this->getHelperData()->getGeneralConfig('active') == '1') {
         return $this->initFunction('Gift Card');
     } else {
         $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
         return $resultRedirect->setPath('csm/noroute');
     }
    }
}
