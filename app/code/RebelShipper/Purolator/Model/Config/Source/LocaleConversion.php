<?php  
namespace RebelShipper\Purolator\Model\Config\Source;
/**
 * @category   RebelShipper
 * @package    RebelShipper_Purolator
 * @author     iam@rebelshipper.com
 * @website    http://www.rebelshipper.com
 */
class LocaleConversion {
    
    // Function to shop options for multi lingual..
    public function toOptionArray()
    {
    $options = array(
    array(
               'value' => "en",
               'label' => "English"
            ),
    array(
               'value' => "fr",
               'label' => "French"
            ),
    array(
               'value' => "choose",
               'label' => "Based on store"
            )
          );
        return $options;
    }

  
}
