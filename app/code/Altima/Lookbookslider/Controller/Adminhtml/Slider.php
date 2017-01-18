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

namespace Altima\Lookbookslider\Controller\Adminhtml;

class Slider extends Actions {

    protected $_formSessionKey = 'lookbookslider_slider_form_data';
    protected $_allowedKey = 'Altima_Lookbookslider::slider';
    protected $_modelClass = 'Altima\Lookbookslider\Model\Slider';
    protected $_activeMenu = 'Altima_Lookbookslider::slider';
    protected $_statusField = 'is_active';
    protected $_paramsHolder = 'slider';

}
