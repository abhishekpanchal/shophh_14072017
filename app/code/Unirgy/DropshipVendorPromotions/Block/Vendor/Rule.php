<?php

namespace Unirgy\DropshipVendorPromotions\Block\Vendor;

use Magento\Config\Model\Config\Source\Yesno;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Data\Form;
use Magento\Framework\Data\Form\AbstractForm;
use Magento\Framework\Date;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\View\LayoutFactory;
use Magento\Rule\Block\Actions;
use Magento\Rule\Block\Conditions as BlockConditions;
use Magento\SalesRule\Helper\Coupon;
use Magento\SalesRule\Model\Rule as ModelRule;
use Magento\SalesRule\Model\RuleFactory;
use Unirgy\DropshipVendorPromotions\Block\Vendor\Rule\Renderer\Fieldset\ActionsFilter;
use Unirgy\DropshipVendorPromotions\Block\Vendor\Rule\Renderer\Fieldset\Conditions;

class Rule extends Template
{
    /**
     * @var RuleFactory
     */
    protected $_ruleFactory;

    /**
     * @var Registry
     */
    protected $_coreRegistry;

    /**
     * @var Conditions
     */
    protected $_fieldsetConditions;

    /**
     * @var BlockConditions
     */
    protected $_blockConditions;

    /**
     * @var Yesno
     */
    protected $_sourceYesno;

    /**
     * @var ActionsFilter
     */
    protected $_fieldsetActionsfilter;

    /**
     * @var Actions
     */
    protected $_blockActions;

    /**
     * @var Coupon
     */
    protected $_helperCoupon;

    protected $_formFactory;

    protected $_hlp;

    public function __construct(
        \Unirgy\Dropship\Helper\Data $udropshipHelper,
        \Magento\Framework\Data\FormFactory $formFactory,
        Context $context,
        RuleFactory $modelRuleFactory,
        Registry $frameworkRegistry,
        Conditions $fieldsetConditions,
        BlockConditions $blockConditions, 
        Yesno $sourceYesno,
        ActionsFilter $fieldsetActionsfilter,
        Actions $blockActions, 
        Coupon $helperCoupon,
        array $data = [])
    {
        $this->_hlp = $udropshipHelper;
        $this->_formFactory = $formFactory;
        $this->_ruleFactory = $modelRuleFactory;
        $this->_coreRegistry = $frameworkRegistry;
        $this->_fieldsetConditions = $fieldsetConditions;
        $this->_blockConditions = $blockConditions;
        $this->_sourceYesno = $sourceYesno;
        $this->_fieldsetActionsfilter = $fieldsetActionsfilter;
        $this->_blockActions = $blockActions;
        $this->_helperCoupon = $helperCoupon;

        parent::__construct($context, $data);
    }

    protected $_form;
    protected $_rule;

    protected function _beforeToHtml()
    {
        parent::_beforeToHtml();

        Form::setFieldsetRenderer(
            $this->getLayout()->createBlock('Unirgy\DropshipVendorPromotions\Block\Vendor\Rule\Renderer\Fieldset')
        );
        Form::setFieldsetElementRenderer(
            $this->getLayout()->createBlock('Unirgy\DropshipVendorPromotions\Block\Vendor\Rule\Renderer\FieldsetElement')
        );

        return $this;
    }
    public function getForm()
    {
        if (null === $this->_form) {
            $rule = $this->getRule();
            $this->_form = $this->_formFactory->create();
            $this->_form->setDataObject($rule);
            $values = $rule->getData();

            if (($udFormData = ObjectManager::getInstance()->get('Unirgy\Dropship\Model\Session')->getUdpromoData(true))
                && is_array($udFormData)
            ) {
                $values = array_merge($values, $udFormData);
            }

            $this->_addGeneralFieldset($rule, $values);
            $this->_addConditionsFieldset($rule, $values);
            $this->_addActionsFieldset($rule, $values);
            $this->_addActionsFilterFieldset($rule, $values);
            //$this->_addCouponsFieldset($rule, $values);

            $this->_form->addValues($values);

            //$this->_form->setFieldNameSuffix('rule');
        }
        return $this->_form;
    }
    public function getRule()
    {
        if (null === $this->_rule) {
            $this->_rule = $this->_ruleFactory->create()->load(
                $this->_request->getParam('id')
            );
            $this->_rule->loadCouponCode();
            $this->_coreRegistry->register('current_promo_quote_rule', $this->_rule);
        }
        return $this->_rule;
    }
    protected function _addGeneralFieldset($rule, &$values)
    {
        $_useDates = !($this->_hlp->isEE() && $this->_hlp->compareMageVer('2.1.2','2.1.2'));
        $fieldset = $this->_form->addFieldset('general_fieldset',
            [
                'legend'=>__('General'),
                'class'=>'fieldset-wide',
        ]);

        $this->_addElementTypes($fieldset);

        $data = new DataObject($values);

        if ($rule->getId()) {
            $fieldset->addField('rule_id', 'hidden', [
                'name' => 'rule_id',
                'is_wide'=>true,
                'is_top'=>true,
                'is_hidden'=>true,
            ]);
        }

        $fieldset->addField('product_ids', 'hidden', [
            'name' => 'product_ids',
            'is_wide'=>true,
            'is_top'=>true,
            'is_hidden'=>true,
        ]);

        $fieldset->addField('name', 'text', [
            'name' => 'name',
            'label' => __('Name'),
            'title' => __('Name'),
            'required' => true,
        ]);

        $fieldset->addField('description', 'textarea', [
            'name' => 'description',
            'label' => __('Description'),
            'title' => __('Description'),
            'style' => 'height: 100px;',
        ]);

        $fieldset->addField('is_active', 'select', [
            'label'     => __('Status'),
            'title'     => __('Status'),
            'name'      => 'is_active',
            'required' => true,
            'options'    => [
                '1' => __('Active'),
                '0' => __('Inactive'),
            ],
        ]);

        if (!$rule->getId()) {
            $rule->setData('is_active', '1');
            $values['is_active'] = 1;
        }

        $usesPerCouponFiled = $fieldset->addField('uses_per_coupon', 'text', [
            'name' => 'uses_per_coupon',
            'label' => __('Uses per Coupon'),
        ]);

        $dateFormatIso = $this->_localeDate->getDateFormat(\IntlDateFormatter::SHORT);
        $fieldset->addField('uses_per_customer', 'text', [
            'name' => 'uses_per_customer',
            'label' => __('Uses per Customer'),
            'note' => __('Usage limit enforced for logged in customers only'),
        ]);
        if ($_useDates) {
            $fieldset->addField('from_date', 'date', [
                'name' => 'from_date',
                'label' => __('From Date'),
                'title' => __('From Date'),
                'input_format' => \Magento\Framework\Stdlib\DateTime::DATE_INTERNAL_FORMAT,
                'format' => $dateFormatIso,
                'date_format' => $this->_hlp->getDateFormatWithLongYear(),
                'class' => 'validate-date validate-date-range date-range-ruledate-from'
            ]);
            $fieldset->addField('to_date', 'date', [
                'name' => 'to_date',
                'label' => __('To Date'),
                'title' => __('To Date'),
                'input_format' => \Magento\Framework\Stdlib\DateTime::DATE_INTERNAL_FORMAT,
                'format' => $dateFormatIso,
                'date_format' => $this->_hlp->getDateFormatWithLongYear(),
                'class' => 'validate-date validate-date-range date-range-ruledate-to'
            ]);
        }

        /*
        $couponTypeFiled = $fieldset->addField('coupon_type', 'select', array(
            'name'       => 'coupon_type',
            'label'      => __('Coupon'),
            'required'   => true,
            'options'    => $this->_modelRuleFactory->create()->getCouponTypes(),
            'is_bottom'=>true,
            'is_wide'=>true
        ));
        */

        $couponCodeFiled = $fieldset->addField('coupon_code', 'text', [
            'name' => 'coupon_code',
            'label' => __('Coupon Code'),
            'required' => false,
            'is_bottom'=>true,
            'is_wide'=>true
        ]);

        /*
        $autoGenerationCheckbox = $fieldset->addField('use_auto_generation', 'checkbox', array(
            'name'  => 'use_auto_generation',
            'label' => __('Use Auto Generation'),
            'note'  => __('If you select and save the rule you will be able to generate multiple coupon codes.'),
            'onclick' => 'handleCouponsTabContentActivity()',
            'checked' => (int)$rule->getUseAutoGeneration() > 0 ? 'checked' : '',
            'is_bottom'=>true,
            'is_wide'=>true
        ));

        $autoGenerationCheckbox->setRenderer(
            $this->_viewLayoutFactory->create()->createBlock('Unirgy\DropshipVendorPromotions\Block\Vendor\Rule\Renderer\Autocheckbox')
        );

        $fieldset->setData('udpromo_form_after', $this->_viewLayoutFactory->create()->createBlock('Magento\Backend\Block\Widget\Form\Element\Dependence')
                ->addFieldMap($couponTypeFiled->getHtmlId(), $couponTypeFiled->getName())
                ->addFieldMap($couponCodeFiled->getHtmlId(), $couponCodeFiled->getName())
                ->addFieldMap($autoGenerationCheckbox->getHtmlId(), $autoGenerationCheckbox->getName())
                ->addFieldMap($usesPerCouponFiled->getHtmlId(), $usesPerCouponFiled->getName())
                ->addFieldDependence(
                    $couponCodeFiled->getName(),
                    $couponTypeFiled->getName(),
                    ModelRule::COUPON_TYPE_SPECIFIC)
                ->addFieldDependence(
                    $autoGenerationCheckbox->getName(),
                    $couponTypeFiled->getName(),
                    ModelRule::COUPON_TYPE_SPECIFIC)
                ->addFieldDependence(
                    $usesPerCouponFiled->getName(),
                    $couponTypeFiled->getName(),
                    ModelRule::COUPON_TYPE_SPECIFIC)
        );
        */

        $this->_prepareFieldsetColumns($fieldset);
        return $this;
    }

    protected function _addConditionsFieldset($rule, &$values)
    {
        $renderer = $this->_fieldsetConditions
            ->setTemplate('Unirgy_DropshipVendorPromotions::unirgy/udpromo/vendor/rule/renderer/fieldset/conditions.phtml')
            ->setNewChildUrl($this->getUrl('udpromo/vendor/newConditionHtml/form/conditions_fieldset'));

        $fieldset = $this->_form->addFieldset('conditions_fieldset',
            [
                'legend'=>__('Conditions [Apply the rule only if the following conditions are met (leave blank for all products)]'),
                'class'=>'fieldset-wide',
            ])->setRenderer($renderer);

        $this->_addElementTypes($fieldset);

        $data = new DataObject($values);

        $fieldset->addField('conditions', 'text', [
            'switch_adminhtml'=>true,
            'name' => 'rule[conditions]',
            'label' => __('Conditions'),
            'title' => __('Conditions'),
            'is_top'=>true,
            'is_wide'=>true,
        ])->setRule($rule)->setRenderer($this->_blockConditions);

        $this->_prepareFieldsetColumns($fieldset);
        return $this;
    }
    protected function _addActionsFieldset($rule, &$values)
    {
        $fieldset = $this->_form->addFieldset('actions_fieldset',
            [
                'legend'=>__('Actions'),
                'class'=>'fieldset-wide',
            ]);
        $this->_addElementTypes($fieldset);

        $data = new DataObject($values);

        $fieldset->addField('simple_action', 'select', [
            'label'     => __('Apply'),
            'name'      => 'simple_action',
            'options'    => [
                ModelRule::BY_PERCENT_ACTION => __('Percent of product price discount'),
                ModelRule::BY_FIXED_ACTION => __('Fixed amount discount'),
                ModelRule::CART_FIXED_ACTION => __('Fixed amount discount for whole cart'),
                ModelRule::BUY_X_GET_Y_ACTION => __('Buy X get Y free (discount amount is Y)'),
            ],
        ]);
        $fieldset->addField('discount_amount', 'text', [
            'name' => 'discount_amount',
            'required' => true,
            'class' => 'validate-not-negative-number',
            'label' => __('Discount Amount'),
        ]);
        $rule->setDiscountAmount($rule->getDiscountAmount()*1);

        $fieldset->addField('discount_qty', 'text', [
            'name' => 'discount_qty',
            'label' => __('Maximum Qty Discount is Applied To'),
        ]);
        $rule->setDiscountQty($rule->getDiscountQty()*1);

        $fieldset->addField('discount_step', 'text', [
            'name' => 'discount_step',
            'label' => __('Discount Qty Step (Buy X)'),
        ]);

        $fieldset->addField('apply_to_shipping', 'select', [
            'label'     => __('Apply to Shipping Amount'),
            'title'     => __('Apply to Shipping Amount'),
            'name'      => 'apply_to_shipping',
            'values'    => $this->_sourceYesno->toOptionArray(),
        ]);

        $fieldset->addField('simple_free_shipping', 'select', [
            'label'     => __('Free Shipping'),
            'title'     => __('Free Shipping'),
            'name'      => 'simple_free_shipping',
            'options'    => [
                0 => __('No'),
                \Magento\OfflineShipping\Model\SalesRule\Rule::FREE_SHIPPING_ITEM => __('For matching items only'),
                \Magento\OfflineShipping\Model\SalesRule\Rule::FREE_SHIPPING_ADDRESS => __('For shipment with matching items'),
            ],
        ]);

        $this->_prepareFieldsetColumns($fieldset);
        return $this;
    }
    protected function _addActionsFilterFieldset($rule, &$values)
    {
        $renderer = $this->_fieldsetActionsfilter
            ->setTemplate('Unirgy_DropshipVendorPromotions::unirgy/udpromo/vendor/rule/renderer/fieldset/actions_filter.phtml')
            ->setNewChildUrl($this->getUrl('udpromo/vendor/newActionHtml/form/actions_filter_fieldset'));

        $fieldset = $this->_form->addFieldset('actions_filter_fieldset',
            [
                'legend'=>__('Actions Filter [Apply the rule only to cart items matching the following conditions (leave blank for all items)]'),
                'class'=>'fieldset-wide',
            ])->setRenderer($renderer);

        $this->_addElementTypes($fieldset);

        $data = new DataObject($values);

        $fieldset->addField('actions', 'text', [
            'name' => 'rule[actions]',
            'label' => __('Apply To'),
            'title' => __('Apply To'),
            'required' => true,
            'is_wide'=>true,
            'is_top'=>true,
        ])->setRule($rule)->setRenderer($this->_blockActions);

        $this->_prepareFieldsetColumns($fieldset);
        return $this;
    }
    protected function _addCouponsFieldset($rule, &$values)
    {
        $renderer = $this->_fieldsetCoupons
            ->setTemplate('Unirgy_DropshipVendorPromotions::unirgy/udpromo/vendor/rule/renderer/fieldset/coupons.phtml');

        $fieldset = $this->_form->addFieldset('coupons_fieldset',
            [
                'legend'=>__('Coupons'),
                'class'=>'fieldset-wide',
            ])->setRenderer($renderer);

        $this->_addElementTypes($fieldset);

        $data = new DataObject($values);
        $couponHelper = $this->_helperCoupon;

        $model = $this->_coreRegistry->registry('current_promo_quote_rule');
        $ruleId = $model->getId();

        $form->setHtmlIdPrefix('coupons_');

        $gridBlock = $this->getLayout()->getBlock('promo_quote_edit_tab_coupons_grid');
        $gridBlockJsObject = '';
        if ($gridBlock) {
            $gridBlockJsObject = $gridBlock->getJsObjectName();
        }

        $fieldset = $form->addFieldset('information_fieldset', ['legend'=>__('Coupons Information')]);
        $fieldset->addClass('ignore-validate');

        $fieldset->addField('rule_id', 'hidden', [
            'name'     => 'rule_id',
            'value'    => $ruleId
        ]);

        $fieldset->addField('qty', 'text', [
            'name'     => 'qty',
            'label'    => __('Coupon Qty'),
            'title'    => __('Coupon Qty'),
            'required' => true,
            'class'    => 'validate-digits validate-greater-than-zero'
        ]);

        $fieldset->addField('length', 'text', [
            'name'     => 'length',
            'label'    => __('Code Length'),
            'title'    => __('Code Length'),
            'required' => true,
            'note'     => __('Excluding prefix, suffix and separators.'),
            'value'    => $couponHelper->getDefaultLength(),
            'class'    => 'validate-digits validate-greater-than-zero'
        ]);

        $fieldset->addField('format', 'select', [
            'label'    => __('Code Format'),
            'name'     => 'format',
            'options'  => $couponHelper->getFormatsList(),
            'required' => true,
            'value'    => $couponHelper->getDefaultFormat()
        ]);

        $fieldset->addField('prefix', 'text', [
            'name'  => 'prefix',
            'label' => __('Code Prefix'),
            'title' => __('Code Prefix'),
            'value' => $couponHelper->getDefaultPrefix()
        ]);

        $fieldset->addField('suffix', 'text', [
            'name'  => 'suffix',
            'label' => __('Code Suffix'),
            'title' => __('Code Suffix'),
            'value' => $couponHelper->getDefaultSuffix()
        ]);

        $fieldset->addField('dash', 'text', [
            'name'  => 'dash',
            'label' => __('Dash Every X Characters'),
            'title' => __('Dash Every X Characters'),
            'note'  => __('If empty no separation.'),
            'value' => $couponHelper->getDefaultDashInterval(),
            'class' => 'validate-digits'
        ]);

        $idPrefix = $form->getHtmlIdPrefix();
        $generateUrl = $this->getGenerateUrl();

        $fieldset->addField('generate_button', 'note', [
            'text' => $this->getButtonHtml(
                    __('Generate'),
                    "generateCouponCodes('{$idPrefix}' ,'{$generateUrl}', '{$gridBlockJsObject}')",
                    'generate'
                )
        ]);

        $this->_prepareFieldsetColumns($fieldset);
        return $this;
    }

    public function getGenerateUrl()
    {
        return $this->getUrl('udpromo/vendor/generate');
    }

    protected function _prepareFieldsetColumns($fieldset)
    {
        $elements = $fieldset->getElements()->getIterator();
        reset($elements);
        $fullCnt = count($elements);
        $wideElementsBottom = $wideElements = $lcElements = $rcElements = [];
        while($element=current($elements)) {
            if ($element->getIsWide()) {
                if ($element->getIsBottom()) {
                    $wideElementsBottom[] = $element->getId();
                } else {
                    $wideElements[] = $element->getId();
                }
                $fullCnt--;
            }
            next($elements);
        }
        $halfCnt = ceil($fullCnt/2);
        reset($elements);
        $i=0; while ($element=current($elements)) {
            if (!$element->getIsWide()) {
                $lcElements[] = $element->getId();
                $i++;
            }
            next($elements);
            if ($i>=$halfCnt) break;
        }
        while ($element=current($elements)) {
            if (!$element->getIsWide()) {
                $rcElements[] = $element->getId();
            }
            next($elements);
        }
        $fieldset->setWideColumnTop($wideElements);
        $fieldset->setWideColumnBottom($wideElementsBottom);
        $fieldset->setLeftColumn($lcElements);
        $fieldset->setRightColumn($rcElements);
        reset($elements);
        return $this;
    }
    protected $_additionalElementTypes = null;
    protected function _initAdditionalElementTypes()
    {
        if (is_null($this->_additionalElementTypes)) {
            $result = [];

            $response = new DataObject();
            $response->setTypes([]);
            $this->_eventManager->dispatch('udpromo_rule_edit_element_types', ['response'=>$response]);

            foreach ($response->getTypes() as $typeName=>$typeClass) {
                $result[$typeName] = $typeClass;
            }
            $this->_additionalElementTypes = $result;
        }
        return $this;
    }

    protected function _getAdditionalElementTypes()
    {
        $this->_initAdditionalElementTypes();
        return $this->_additionalElementTypes;
    }
    public function addAdditionalElementType($code, $class)
    {
        $this->_initAdditionalElementTypes();
        $this->_additionalElementTypes[$code] = Mage::getConfig()->getBlockClassName($class);
        return $this;
    }

    protected function _addElementTypes(AbstractForm $baseElement)
    {
        $types = $this->_getAdditionalElementTypes();
        foreach ($types as $code => $className) {
            $baseElement->addType($code, $className);
        }
    }
}