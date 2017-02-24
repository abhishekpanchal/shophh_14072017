<?php

namespace Unirgy\DropshipVendorPromotions\Controller\Vendor;

use Magento\Backend\Model\View\Result\ForwardFactory;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Customer\Model\ResourceModel\Group\Collection;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\DataObject;
use Magento\Framework\Exception;
use Magento\Framework\HTTP\Header;
use Magento\Framework\Registry;
use Magento\Framework\View\DesignInterface;
use Magento\Framework\View\LayoutFactory;
use Magento\Framework\View\Result\PageFactory;
use Magento\SalesRule\Model\Rule;
use Magento\SalesRule\Model\RuleFactory;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;
use Unirgy\Dropship\Helper\Data as HelperData;

class RulePost extends AbstractVendor
{
    protected function _filterDates($requestData)
    {
        $dateFilter = $this->_hlp->getObj('\Magento\Framework\Stdlib\DateTime\Filter\Date');
        $inputFilter = new \Zend_Filter_Input(
            ['from_date' => $dateFilter, 'to_date' => $dateFilter],
            [],
            $requestData
        );
        $requestData = $inputFilter->getUnescaped();
        return $requestData;
    }
    public function execute()
    {
        $session = ObjectManager::getInstance()->get('Unirgy\Dropship\Model\Session');

        if ($this->getRequest()->getPost()) {
            try {
                /** @var $model Rule */
                $model = $this->_ruleFactory->create();
                $this->_eventManager->dispatch(
                    'adminhtml_controller_salesrule_prepare_save',
                    ['request' => $this->getRequest()]);
                $data = (array)$this->getRequest()->getPost();
                $data = $this->_filterDates($data);
                $id = $this->getRequest()->getParam('rule_id');
                if ($id) {
                    $model->load($id);
                    if ($id != $model->getId() || $model->getUdropshipVendor()!=$session->getVendorId()) {
                        throw new \Exception(__('Wrong rule specified.'));
                    }
                } else {
                    $cGroups = $this->_hlp->createObj('\Magento\Customer\Model\ResourceModel\Group\Collection');
                    $data['customer_group_ids'] = [];
                    foreach ($cGroups as $cGroup) {
                        $data['customer_group_ids'][] = $cGroup->getId();
                    }
                    $websites = $this->_storeManager->getWebsites(true);
                    foreach ($websites as $website) {
                        $data['website_ids'][] = $website->getId();
                    }
                    $data['udropship_vendor'] = $session->getVendorId();
                }

                $validateResult = $model->validateData(new DataObject($data));
                if ($validateResult !== true) {
                    foreach($validateResult as $errorMessage) {
                        $this->messageManager->addError($errorMessage);
                    }
                    $session->setUdpromoData($data);
                    return $this->_redirect('*/*/ruleEdit', ['id'=>$model->getId()]);
                }

                if (isset($data['simple_action']) && $data['simple_action'] == 'by_percent'
                    && isset($data['discount_amount'])) {
                    $data['discount_amount'] = min(100,$data['discount_amount']);
                }
                if (isset($data['rule']['conditions'])) {
                    $data['conditions'] = $data['rule']['conditions'];
                }
                if (isset($data['rule']['actions'])) {
                    $data['actions'] = $data['rule']['actions'];
                }
                unset($data['rule']);
                $data['stop_rules_processing'] = 0;
                $data['coupon_code'] = $this->getRequest()->getParam('coupon_code');
                if (!empty($data['coupon_code'])) $data['coupon_type'] = Rule::COUPON_TYPE_SPECIFIC;
                $model->loadPost($data);

                $session->setUdpromoData($model->getData());

                $model->save();
                $this->messageManager->addSuccess(__('The rule has been saved.'));
                $session->setUdpromoData(false);
                if ($this->getRequest()->getParam('back')) {
                    return $this->_redirect('*/*/ruleEdit', ['id' => $model->getId()]);
                }
                return $this->_redirect('*/*/');
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
                $id = (int)$this->getRequest()->getParam('rule_id');
                if (!empty($id)) {
                    return $this->_redirect('*/*/ruleEdit', ['id' => $id]);
                } else {
                    return $this->_redirect('*/*/ruleNew');
                }

            } catch (\Exception $e) {
                $this->messageManager->addError(
                    __('An error occurred while saving the rule data. Please review the log and try again.'));
                $this->_hlp->logError($e);
                $session->setUdpromoData($data);
                return $this->_redirect('*/*/ruleEdit', ['id' => $this->getRequest()->getParam('rule_id')]);
            }
        }
        $this->messageManager->addError(__('Unable to find a data to save'));
        return $this->_redirectRuleAfterPost();
    }
}
