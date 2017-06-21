<?php
/**
  * You are allowed to use this API in your web application.
 *
 * Copyright (C) 2016 by customweb GmbH
 *
 * This program is licenced under the customweb software licence. With the
 * purchase or the installation of the software in your application you
 * accept the licence agreement. The allowed usage is outlined in the
 * customweb software licence which can be found under
 * http://www.sellxed.com/en/software-license-agreement
 *
 * Any modification or distribution is strictly forbidden. The license
 * grants you the installation in one application. For multiuse you will need
 * to purchase further licences at http://www.sellxed.com/shop.
 *
 * See the customweb software licence agreement for more details.
 *
 */

//require_once 'Customweb/Core/Util/Class.php';


/**
 * This Util class allows a convient way to serialize and unserialize of
 * objects.
 *
 * In case of unserializing the class checks if any class must be loaded. In
 * case a class is not loaded the method tries to load it with the library
 * class loader. In case this does not work, the registred callbacks are called.
 *
 * @author Thomas Hunziker / Simon Schurter
 *
 */
final class Customweb_Core_Util_Serialization {

	private function __construct() {}

	/**
	 * Serializes a object into a string representation.
	 *
	 * @param object $object
	 * @return string
	 */
	public static function serialize($object) {
		return base64_encode(serialize($object));
	}

	/**
	 * Unserializes a object from a string representation produced by
	 * Serialization::serialize().
	 *
	 * @param string $data
	 * @return mixed
	 * @throws Customweb_Core_Exception_ClassNotFoundException
	 */
	public static function unserialize($data) {
		$serializedString = base64_decode($data);
		self::preloadClasses($serializedString);
		return unserialize($serializedString);
	}
	
	/**
	 * Serializes a object into a binary representation using gzcompress.
	 *
	 * @param object $object
	 * @return binary
	 */
	public static function serializeBinary($object){
		return gzcompress(serialize($object));
	}
	
	/**
	 * Unserializes a object from a binary representation produced by
	 * Serialization::serializeBinary().
	 *
	 * @param binary $data
	 * @return mixed
	 * @throws Customweb_Core_Exception_ClassNotFoundException
	 */
	public static function unserializeBinary($data) {
		$serializedString = gzuncompress($data);
		self::preloadClasses($serializedString);
		return unserialize($serializedString);
	}

	/**
	 * @param string $serializedString
	 * @throws Customweb_Core_Exception_ClassNotFoundException
	 */
	private static function preloadClasses($serializedString) {
		$matches = array();
		preg_match_all('/O:[0-9]+:\"(.+?)\":/', self::cleanStringPartsOut($serializedString), $matches);
		if (isset($matches[1])) {
			foreach ($matches[1] as $match) {
				$className = $match;
				if (!Customweb_Core_Util_Class::isClassLoaded($className)) {
					try {
						Customweb_Core_Util_Class::loadLibraryClassByName($className);
					}
					catch(Customweb_Core_Exception_ClassNotFoundException $e) {
						if (!class_exists($className)) {
							throw $e;
						}
					}
				}
			}
		}
	}
	
	/**
	 * This method removes all string parts within the serialized string (i.e. all string properties). This is required because the 
	 * serialized string may contain an object which has a string property which contains again serialized 
	 * data. In this situation we would search for classes in the string which may never required, because 
	 * during the deserialization the class is never instantiated and as such we may load a class which is
	 * not required.  
	 * 
	 * @param string $serializedString
	 * @throws Exception thrown when the provided serialized string is invalid.
	 * @return string the cleaned string.
	 */
	private static function cleanStringPartsOut($serializedString) {
		while(true) {
			$pos = strpos($serializedString, 's:');
			if ($pos !== false) {
				$remainer = substr($serializedString, $pos + 2);
				$matches = array();
				if (preg_match('/^([0-9]+)/', $remainer, $matches)) {
					$cutoff = strlen($matches[1]) + 3 + (int)$matches[1];
					if ($cutoff > strlen($remainer)) {
						throw new Exception("The serialized string ends unexpected. Has the serialized string eventually be truncated unintentionally?");
					}
					$serializedString = substr($serializedString, 0, $pos) . substr($remainer, $cutoff);
				}
				else {
					throw new Exception("The provided serialized string is invalid. There is indicated a string sequence, however the length is not specified.");
				}
			}
			else {
				return $serializedString;
			}
		}
	}
	

}