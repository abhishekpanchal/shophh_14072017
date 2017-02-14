<?php
 
namespace Bg\Freshdesk\Model\Config\Source;
 
class Customers implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
 
        return [
            ['value' => 0, 'label' => __('No, They will have to use our freshdesk portal')],
            ['value' => 1, 'label' => __('Yes, My accounts will have many tickets to view, create')],
          ];
    }
}
