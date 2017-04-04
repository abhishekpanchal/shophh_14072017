<?php  
namespace RebelShipper\CanadaPost\Model\Config\Source;
/**
 * @category   RebelShipper
 * @package    RebelShipper_CanadaPost
 * @author     iam@rebelshipper.com
 * @website    http://www.rebelshipper.com
 */
class ShipmentTypes {
    public function toOptionArray() {
    $options = array(
    
array("value"=>"DOM.RP","label"=>"Regular Parcel"),
array("value"=>"DOM.EP","label"=>"Expedited Parcel"),
array("value"=>"DOM.XP","label"=>"Xpresspost"),
array("value"=>"DOM.XP.CERT","label"=>"Xpresspost Certified"),
array("value"=>"DOM.PC","label"=>"Priority"),
array("value"=>"DOM.LIB","label"=>"Library Books"),
array("value"=>"USA.EP","label"=>"Expedited Parcel USA"),
array("value"=>"USA.PW.ENV","label"=>"Priority Worldwide Envelope USA"),
array("value"=>"USA.PW.PAK","label"=>"Priority Worldwide pak USA"),
array("value"=>"USA.PW.PARCEL","label"=>"Priority Worldwide Parcel USA"),
array("value"=>"USA.SP.AIR","label"=>"Small Packet USA Air"),
array("value"=>"USA.TP","label"=>"Tracked Packet USA"),
array("value"=>"USA.TP.LVM","label"=>"Tracked Packet USA (LVM)"),
array("value"=>"USA.XP","label"=>"Xpresspost USA"),
array("value"=>"INT.XP","label"=>"Xpresspost International"),
array("value"=>"INT.IP.AIR","label"=>"International Parcel Air"),
array("value"=>"INT.IP.SURF","label"=>"International Parcel Surface"),
array("value"=>"INT.PW.ENV","label"=>"Priority Worldwide Envelope Int’l"),
array("value"=>"INT.PW.PAK","label"=>"Priority Worldwide pak Int’l"),
array("value"=>"INT.PW.PARCEL","label"=>"Priority Worldwide parcel Int’l"),
array("value"=>"INT.SP.AIR","label"=>"Small Packet International Air"),
array("value"=>"INT.SP.SURF","label"=>"Small Packet International Surface"),
array("value"=>"INT.TP","label"=>"Tracked Packet International"),

    
    array(
               'label' => "Lowest Price Free Shipping",
               'value' => "LowestPriceFreeShipping"
            ),
         );
        return $options;
    }

  
}
