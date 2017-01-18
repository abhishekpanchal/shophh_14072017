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

class Slider extends \Magento\Backend\Block\Widget\Grid\Container {

    protected function _construct() {
        $this->_controller     = 'adminhtml';
        $this->_blockGroup     = 'Altima_Lookbookslider';
        $this->_headerText     = __('Slider');
        $this->_addButtonLabel = __('Add New Slider');
        parent::_construct();
    }

}
