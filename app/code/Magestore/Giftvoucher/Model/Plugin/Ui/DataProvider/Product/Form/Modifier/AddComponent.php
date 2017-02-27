<?php
namespace Magestore\Giftvoucher\Model\Plugin\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\Attributes;

class AddComponent
{
    /**
     * @return array
     */
    public function afterModifyMeta(Attributes $attributes, $meta)
    {
        if (isset($meta['prices']['children']['container_gift_type']['children']['gift_type']['arguments']['data']['config']['component']))
        $meta['prices']['children']['container_gift_type']['children']['gift_type']['arguments']['data']['config']['component'] = 'Magestore_Giftvoucher/js/form/giftType';
        if (isset($meta['prices']['children']['container_gift_price_type']['children']['gift_price_type']['arguments']['data']['config']['component']))
        $meta['prices']['children']['container_gift_price_type']['children']['gift_price_type']['arguments']['data']['config']['component'] = 'Magestore_Giftvoucher/js/form/giftPriceType';
//        $meta['prices']['children']['container_gift_value']['children']['gift_value']['arguments']['data']['config']['component'] = 'Magestore_Giftvoucher/js/form/giftValue';
//        $meta['prices']['children']['container_gift_from']['children']['gift_from']['arguments']['data']['config']['component'] = 'Magestore_Giftvoucher/js/form/giftFrom';
//        $meta['prices']['children']['container_gift_to']['children']['gift_to']['arguments']['data']['config']['component'] = 'Magestore_Giftvoucher/js/form/giftTo';
//        $meta['prices']['children']['container_gift_dropdown']['children']['gift_dropdown']['arguments']['data']['config']['component'] = 'Magestore_Giftvoucher/js/form/giftDropdown';
//        $meta['prices']['children']['container_gift_price']['children']['gift_price']['arguments']['data']['config']['component'] = 'Magestore_Giftvoucher/js/form/giftPrice';
//
//
//        $meta['prices']['children']['container_gift_price_type']['children']['gift_price_type']['arguments']['data']['config']['elementTmpl'] = 'Magestore_Giftvoucher/gift-price-type';


        return $meta;
    }

//    function xlog($message = 'null')
//    {
//        $log = print_r($message, true);
//        \Magento\Framework\App\ObjectManager::getInstance()
//            ->get('Psr\Log\LoggerInterface')
//            ->debug($log);
//    }
}