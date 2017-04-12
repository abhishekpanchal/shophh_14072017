<?php
namespace Hhmedia\Collection\Block\Adminhtml\Collection\Edit\Tab;

/**
 * Cms page edit form main tab
 */
class Main extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $_systemStore;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Store\Model\System\Store $systemStore
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Store\Model\System\Store $systemStore,
        array $data = []
    ) {
        $this->_systemStore = $systemStore;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare form
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        /* @var $model \Magento\Cms\Model\Page */
        $model = $this->_coreRegistry->registry('collection');

        /*
         * Checking if user have permissions to save information
         */
        if ($this->_isAllowedAction('Hhmedia_Collection::save')) {
            $isElementDisabled = false;
        } else {
            $isElementDisabled = true;
        }

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();

        $form->setHtmlIdPrefix('collection_main_');

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Collection Information')]);

        if ($model->getId()) {
            $fieldset->addField('collection_id', 'hidden', ['name' => 'collection_id']);
        }

        $fieldset->addField(
            'collection_title',
            'text',
            [
                'name' => 'collection_title',
                'label' => __('Collection Title'),
                'title' => __('Collection Title'),
                'required' => true,
                'disabled' => $isElementDisabled
            ]
        );

        $fieldset->addField(
            'collection_subtitle',
            'text',
            [
                'name' => 'collection_subtitle',
                'label' => __('Collection Subtitle'),
                'title' => __('Collection Subtitle'),
                'required' => true,
                'disabled' => $isElementDisabled
            ]
        );

        $fieldset->addField(
            'link_text',
            'text',
            [
                'name' => 'link_text',
                'label' => __('Link Text'),
                'title' => __('Link Text'),
                'required' => true,
                'disabled' => $isElementDisabled
            ]
        );

        $fieldset->addField(
            'link_url',
            'text',
            [
                'name' => 'link_url',
                'label' => __('Link URL'),
                'title' => __('Link URL'),
                'required' => true,
                'disabled' => $isElementDisabled
            ]
        );
        
        $this->_eventManager->dispatch('adminhtml_collection_edit_tab_main_prepare_form', ['form' => $form]);

        $form->setValues($model->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * Prepare label for tab
     *
     * @return string
     */
    public function getTabLabel()
    {
        return __('Collection Information');
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return __('Collection Information');
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Check permission for passed action
     *
     * @param string $resourceId
     * @return bool
     */
    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }
}
