<?php
 
namespace Bg\Freshdesk\Model\Config\Source;
 
class Contact implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
 
        return [
            ['value' => 0, 'label' => __('No')],
            ['value' => 1, 'label' => __('Yes')],
          ];
    }
}
