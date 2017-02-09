<?php
namespace Hhmedia\Magazine\Block\Adminhtml\Magazine\Edit\Tab;

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
        $model = $this->_coreRegistry->registry('magazine');

        /*
         * Checking if user have permissions to save information
         */
        if ($this->_isAllowedAction('Hhmedia_Magazine::save')) {
            $isElementDisabled = false;
        } else {
            $isElementDisabled = true;
        }

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();

        $form->setHtmlIdPrefix('magazine_main_');

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Magazine Information')]);

        if ($model->getId()) {
            $fieldset->addField('magazine_id', 'hidden', ['name' => 'magazine_id']);
        }

        $fieldset->addField(
            'title',
            'text',
            [
                'name' => 'title',
                'label' => __('Magazine Title'),
                'title' => __('Magazine Title'),
                'required' => true,
                'disabled' => $isElementDisabled
            ]
        );

        $fieldset->addField(
            'volume_name',
            'text',
            [
                'name' => 'volume_name',
                'label' => __('Volume Name'),
                'title' => __('Volume Name'),
                'required' => true,
                'disabled' => $isElementDisabled
            ]
        );

        $fieldset->addField(
            'status', 
            'select', 
            [
                'label'    => __('Status'),
                'title'    => __('Status'),
                'name'     => 'status',
                'required' => true,
                'options'  => $model->getAvailableStatuses(),
                'disabled' => $isElementDisabled
            ]
        );

        $fieldset->addField(
            'sort_order', 
            'text', 
            [
                'label'    => __('Sort Order'),
                'title'    => __('Sort Order'),
                'name'     => 'sort_order',
                'required' => true,
                'disabled' => $isElementDisabled
            ]
        );
        
        /*$dateFormat = $this->_localeDate->getDateFormat(\IntlDateFormatter::SHORT);
        $fieldset->addField('published_at', 'date', [
            'name'     => 'published_at',
            'date_format' => $dateFormat,
            'image'    => $this->getViewFileUrl('images/grid-cal.gif'),
            'value' => $model->getPublishedAt(),
            'label'    => __('Publishing Date'),
            'title'    => __('Publishing Date'),
            'required' => true
        ]);*/
        
        $this->_eventManager->dispatch('adminhtml_magazine_edit_tab_main_prepare_form', ['form' => $form]);

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
        return __('Magazine Information');
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return __('Magazine Information');
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
