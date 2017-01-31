<?php

/**
 * Altima Lookbook Professional Extension
 *
 * Altima web systems.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is available through the world-wide-web at this URL:
 * http://shop.altima.net.au/tos
 *
 * @category   Altima
 * @package    Altima_LookbookProfessional
 * @author     Altima Web Systems http://altimawebsystems.com/
 * @license    http://shop.altima.net.au/tos
 * @email      support@altima.net.au
 * @copyright  Copyright (c) 2016 Altima Web Systems (http://altimawebsystems.com/)
 */

namespace Altima\Lookbookslider\Block\Adminhtml;

class Slide extends \Magento\Backend\Block\Widget\Grid\Container {

    protected function _construct() {
        $slider_id         = $this->getRequest()->getParam('slider_id');
        $this->_controller = 'adminhtml';
        $this->_blockGroup = 'Altima_Lookbookslider';
        $this->_headerText = __('Shot');
        parent::_construct();

        //$this->addButton('back', array(
        //    'label'   => __("Back to slider list"),
        //    'onclick' => 'setLocation(\'' . $this->getUrl('*/slider/index/') . '\')',
        //    'class'   => 'back',
        //));

        $this->addButton('add', array(
            'label'   => __('Add New Shot'),
            'onclick' => 'setLocation(\'' . $this->getUrl('*/*/new/', array('slider_id' => $slider_id)) . '\')',
            'class'   => 'add',
        ));
    }

    protected function _isAllowedAction($resourceId) {
        return $this->_authorization->isAllowed($resourceId);
    }

}
