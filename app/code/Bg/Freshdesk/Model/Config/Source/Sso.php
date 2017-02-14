<?php
 
namespace Bg\Freshdesk\Model\Config\Source;
 
class Sso implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
 
        return [
            ['value' => 0, 'label' => __('Off')],
            ['value' => 1, 'label' => __('On')],
          ];
    }
}
