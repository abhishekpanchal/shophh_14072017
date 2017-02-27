<?php

namespace Unirgy\Dropship\Block\Adminhtml\SystemConfigFormField;

class FieldContainer extends \Magento\Backend\Block\Template
{
    /**
     * @var \Unirgy\Dropship\Helper\Data
     */
    protected $_hlp;

    public function __construct(
        \Unirgy\Dropship\Helper\Data $udropshipHelper,
        \Magento\Backend\Block\Template\Context $context,
        array $data = []
    )
    {
        $this->_hlp = $udropshipHelper;
        parent::__construct($context, $data);
    }
    public function getStore()
    {
        return $this->_hlp->getDefaultStoreView();
    }

    public function setFieldName($fName)
    {
        $this->resetIdSuffix();
        return $this->setData('field_name', $fName);
    }

    public function getFieldName()
    {
        return $this->getData('field_name')
            ? $this->getData('field_name')
            : ($this->getElement() ? $this->getElement()->getName() : '');
    }

    protected $_idSuffix;
    public function resetIdSuffix()
    {
        $this->_idSuffix = null;
        return $this;
    }
    public function getIdSuffix()
    {
        if (null === $this->_idSuffix) {
            $this->_idSuffix = $this->prepareIdSuffix($this->getFieldName());
        }
        return $this->_idSuffix;
    }

    public function prepareIdSuffix($id)
    {
        return preg_replace('/[^a-zA-Z0-9\$]/', '_', $id);
    }

    public function suffixId($id)
    {
        return $id.$this->getIdSuffix();
    }

    public function getAddButtonId()
    {
        return $this->suffixId('addBtn');
    }
}