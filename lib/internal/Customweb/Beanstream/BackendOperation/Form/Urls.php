<?php

/**
 *  * You are allowed to use this API in your web application.
 *
 * Copyright (C) 2016 by customweb GmbH
 *
 * This program is licenced under the customweb software licence. With the
 * purchase or the installation of the software in your application you
 * accept the licence agreement. The allowed usage is outlined in the
 * customweb software licence which can be found under
 * http://www.sellxed.com/en/software-license-agreement
 *
 * Any modification or distribution is strictly forbidden. The license
 * grants you the installation in one application. For multiuse you will need
 * to purchase further licences at http://www.sellxed.com/shop.
 *
 * See the customweb software licence agreement for more details.
 *
 */

//require_once 'Customweb/Payment/BackendOperation/Form/Abstract.php';
//require_once 'Customweb/Form/Element.php';
//require_once 'Customweb/Form/ElementGroup.php';
//require_once 'Customweb/I18n/Translation.php';
//require_once 'Customweb/Form/Control/Html.php';
//require_once 'Customweb/Form/WideElement.php';



/**
 * @BackendForm
 */
final class Customweb_Beanstream_BackendOperation_Form_Urls extends Customweb_Payment_BackendOperation_Form_Abstract {

	public function getTitle(){
		return Customweb_I18n_Translation::__("Setup");
	}

	public function getElementGroups(){
		return array(
			$this->getSetupGroup(),
			$this->getUrlGroup() 
		);
	}

	private function getUrlGroup(){
		$group = new Customweb_Form_ElementGroup();
		$group->setTitle('URLs');
		$group->addElement($this->getNotificationUrlElement());
		return $group;
	}

	private function getNotificationUrlElement(){
		$control = new Customweb_Form_Control_Html('notificationURL', $this->getEndpointAdapter()->getUrl('ppProcess', 'process'));
		$element = new Customweb_Form_Element(Customweb_I18n_Translation::__("Notification URL"), $control);
		$element->setDescription(
				Customweb_I18n_Translation::__(
						"This URL has to be placed in the backend of Beanstream under administration > account settings > order settings > Response Notification > Payment Gateway."));
		return $element;
	}
	
	private function getSetupGroup() {
		$group = new Customweb_Form_ElementGroup();
		$group->setTitle(Customweb_I18n_Translation::__("Short Installation Instructions:"));
	
		$control = new Customweb_Form_Control_Html('description', Customweb_I18n_Translation::__('This is a brief instruction of the main and most important installation steps, which need to be performed when installing the Beanstream module. For detailed instructions regarding additional and optional settings, please refer to the enclosed instructions in the zip.'));
		$element = new Customweb_Form_WideElement($control);
		$group->addElement($element);
	
		$control = new Customweb_Form_Control_Html('steps', '<ol>
					<li>'.Customweb_I18n_Translation::__('Copy the API Access Passcode and the Haskey into the main module. You find this settings under Administration > Account Settings > Order Settings. Make sure that the Hash algorithm is set correctly in the module.').'</li>
					<li>'.Customweb_I18n_Translation::__('Set the API access passcode into the main module. You will find the API access passcode under Configuration > Payment Profile Configuration.').'</li>
					<li>'.Customweb_I18n_Translation::__('Set the notification URL as outlined below.').'</li>
				</ol>');
		$element = new Customweb_Form_WideElement($control);
		$group->addElement($element);
		return $group;
	}

}