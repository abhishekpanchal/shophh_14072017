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

use Magento\PageCache\Model\Cache\Type as Cache;

abstract class Actions extends \Magento\Backend\App\Action {

    protected $_formSessionKey;
    protected $_allowedKey;
    protected $_modelClass;
    protected $_activeMenu;
    protected $_configSection;
    protected $_idKey = 'id';
    protected $_statusField = 'status';
    protected $_paramsHolder;
    protected $_model;
    protected $_coreRegistry = null;

    public function execute() {
        $_preparedActions = array('index', 'grid', 'new', 'edit', 'save', 'delete', 'config', 'massStatus', 'manage');
        $_action = $this->getRequest()->getActionName();

        if (in_array($_action, $_preparedActions)) {
            $method = '_' . $_action . 'Action';

            $this->_beforeAction();
            $this->$method();
            $this->_afterAction();
        }
    }

    protected function _indexAction() {
        if ($this->getRequest()->getParam('ajax')) {
            $this->_forward('grid');
            return;
        }
        $this->_view->loadLayout();
        $this->_setActiveMenu($this->_activeMenu);
        $title = __('Manage ' . $this->_getModel(false)->getOwnTitle(true));
        $this->_view->getPage()->getConfig()->getTitle()->prepend($title);
        $this->_addBreadcrumb($title, $title);
        $this->_view->renderLayout();
    }

    protected function _gridAction() {
        $this->_view->loadLayout(false);
        $this->_view->renderLayout();
    }

    protected function _newAction() {
        $slider_id = $this->getRequest()->getParam('slider_id');
        $this->_forward('edit', null, null, array('slider_id' => $slider_id));
    }

    protected function _manageAction() {
        $this->_view->loadLayout();
        //$this->_setActiveMenu($this->_activeMenu);
        //$title = __('Manage '.$this->_getModel(false)->getOwnTitle(true));
        //$this->_view->getPage()->getConfig()->getTitle()->prepend($title);
        //$this->_addBreadcrumb($title, $title);
        $this->_view->renderLayout();
    }

    public function _editAction() {
        $model = $this->_getModel();
        $this->_getRegistry()->register('current_model', $model);
        $this->_view->loadLayout();
        $this->_setActiveMenu($this->_activeMenu);
        $title = $model->getOwnTitle();

        if ($model->getId()) {
            $breadcrumbTitle = __('Edit ' . $title);
            $breadcrumbLabel = $breadcrumbTitle;
        } else {
            $breadcrumbTitle = __('New ' . $title);
            $breadcrumbLabel = __('Create ' . $title);
        }
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__($title));
        $this->_view->getPage()->getConfig()->getTitle()->prepend(
                $model->getId() ? $this->_getModelName($model) : __('New ' . $title)
        );

        $this->_addBreadcrumb($breadcrumbLabel, $breadcrumbTitle);

        // restore data
        $values = $this->_getSession()->getData($this->_formSessionKey, true);
        if ($this->_paramsHolder) {
            $values = isset($values[$this->_paramsHolder]) ? $values[$this->_paramsHolder] : null;
        }

        if ($values) {
            $model->addData($values);
        }
        $slider_id = $this->getRequest()->getParam('slider_id');
        if ($slider_id) {
            $model->setData('slider_id', $slider_id);
        }
        $this->_view->renderLayout();
    }

    protected function _getModelName(\Magento\Framework\Model\AbstractModel $model) {
        return $model->getName() ? : $model->getTitle();
    }

    public function _saveAction() {
        $request = $this->getRequest();
        if (!$request->isPost()) {
            $this->getResponse()->setRedirect($this->getUrl('*/*'));
        }
        $model = $this->_getModel();
        try {
            $params = $this->_paramsHolder ? $request->getParam($this->_paramsHolder) : $request->getParams();
            $model->addData($params);
            $this->_beforeSave($model, $request);
            $model->save();
            $this->_afterSave($model, $request);
            $this->messageManager->addSuccess(__($model->getOwnTitle() . ' has been saved.'));
            $this->_setFormData(false);

            if ($request->getParam('back')) {
                $this->_redirect('*/*/edit', [$this->_idKey => $model->getId()]);
            } else {
                if ($model->getSliderId()):
                    $this->_redirect('*/*', array('slider_id' => $model->getSliderId()));
                else:
                    $this->_redirect('*/*');
                endif;
            }
            return;
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addError(nl2br($e->getMessage()));
            $this->_setFormData();
        } catch (\Exception $e) {
            $this->messageManager->addException($e, __('Something went wrong while saving this ' . strtolower($model->getOwnTitle()) . '.') . ' ' . $e->getMessage());
            $this->_setFormData();
        }
        $this->_redirect('*/*/edit', [$this->_idKey => $model->getId()]);
    }

    protected function _beforeSave($model, $request) {
        
    }

    protected function _afterSave($model, $request) {
        $model->cleanMageCache();
    }

    protected function _beforeAction() {
        
    }

    protected function _afterAction() {
        
    }

    protected function _deleteAction() {
        $ids = $this->getRequest()->getParam($this->_idKey);

        if (!is_array($ids)) {
            $ids = [$ids];
        }

        $error = false;
        try {
            foreach ($ids as $id) {
                $this->_objectManager->create($this->_modelClass)->setId($id)->delete();
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $error = true;
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $error = true;
            $this->messageManager->addException($e, __('We can\'t delete ' . strtolower($this->_getModel(false)->getOwnTitle()) . ' right now. ' . $e->getMessage()));
        }

        if (!$error) {
            $this->messageManager->addSuccess(
                    __($this->_getModel(false)->getOwnTitle(count($ids) > 1) . ' have been deleted.')
            );
        }

        //if($model->getSliderId()):
        //$this->_redirect('*/*', array('slider_id' => $model->getSliderId()));
        //    else:
        $this->_redirect('*/*');
        //endif;
    }

    protected function _massStatusAction() {
        $ids = $this->getRequest()->getParam($this->_idKey);

        if (!is_array($ids)) {
            $ids = [$ids];
        }

        $model = $this->_getModel(false);

        $error = false;

        try {

            $status = $this->getRequest()->getParam('status');
            $statusFieldName = $this->_statusField;

            if (is_null($status)) {
                throw new \Magento\Exception("Parameter Status missing in request data.");
            }

            if (is_null($statusFieldName)) {
                throw new \Magento\Exception("Status Field Name is not specified.");
            }

            foreach ($ids as $id) {
                $this->_objectManager->create($this->_modelClass)
                        ->load($id)
                        ->setData($this->_statusField, $status)
                        ->save();
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $error = true;
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $error = true;
            $this->messageManager->addException($e, __('We can\'t change status of ' . strtolower($model->getOwnTitle()) . ' right now. ' . $e->getMessage()));
        }

        if (!$error) {
            $this->messageManager->addSuccess(
                    __($model->getOwnTitle(count($ids) > 1) . ' status have been changed.')
            );
        }

        $this->_redirect('*/*');
    }

    protected function _configAction() {
        $this->_redirect('admin/system_config/edit', ['section' => $this->_configSection()]);
    }

    protected function _setFormData($data = null) {
        $this->_getSession()->setData($this->_formSessionKey, is_null($data) ? $this->getRequest()->getParams() : $data);

        return $this;
    }

    protected function _getRegistry() {
        if (is_null($this->_coreRegistry)) {
            $this->_coreRegistry = $this->_objectManager->get('\Magento\Framework\Registry');
        }
        return $this->_coreRegistry;
    }

    protected function _isAllowed() {
        return $this->_authorization->isAllowed($this->_allowedKey);
    }

    protected function _getModel($load = true) {
        if (is_null($this->_model)) {
            $this->_model = $this->_objectManager->create($this->_modelClass);

            $id = (int) $this->getRequest()->getParam($this->_idKey);
            if ($id && $load) {
                $this->_model->load($id);
            }
        }
        return $this->_model;
    }

}
