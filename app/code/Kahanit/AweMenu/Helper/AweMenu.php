<?php
/**
 * Awe Menu is quick, easy to setup and WYSIWYG menu management system
 *
 * Awe Menu by Kahanit(https://www.kahanit.com) is licensed under a
 * Creative Creative Commons Attribution-NoDerivatives 4.0 International License.
 * Based on a work at https://www.kahanit.com.
 * Permissions beyond the scope of this license may be available at https://www.kahanit.com.
 * To view a copy of this license, visit http://creativecommons.org/licenses/by-nd/4.0/.
 *
 * @author    Amit Sidhpura <amit@kahanit.com>
 * @copyright 2016 Kahanit
 * @license   http://creativecommons.org/licenses/by-nd/4.0/
 * @version   1.0.1.0
 */

namespace Kahanit\AweMenu\Helper;

class AweMenu extends \Magento\Framework\App\Helper\AbstractHelper
{
    public function addslashes($str, $enclosed = false)
    {
        if (is_array($str)) {
            foreach ($str as &$item) {
                $item = self::addslashes($item, $enclosed);
            }

            return $str;
        }

        $search = ['\\', '\0', '\n', '\r', '\x1a', "'", '"'];
        $replace = ['\\\\', '\\0', '\\n', '\\r', '\Z', "\'", '\"'];

        if ($enclosed) {
            $str = self::addslashes($str);
        }

        return str_replace($search, $replace, $str);
    }

    public function stripslashes($str)
    {
        if (is_array($str)) {
            foreach ($str as &$item) {
                $item = self::stripslashes($item);
            }

            return $str;
        }

        $search = ['\\\\', '\\0', '\\n', '\\r', '\Z', "\'", '\"'];
        $replace = ['\\', '\0', '\n', '\r', '\x1a', "'", '"'];

        return str_replace($search, $replace, $str);
    }

    public function filterSearchQuery($search_query = '', $keys = [])
    {
        $search_query = trim($search_query);
        $search_items = [];

        foreach ($keys as $key) {
            $pattern = '/' . $key . '\s*:\s*[^:]*((?=' . implode('\s*:)|(?=', $keys) . '\s*:)|($))/i';
            preg_match($pattern, $search_query, $matches);

            if (isset($matches[0])) {
                $search_value = preg_replace('/\s+/', ' ', $matches[0]);
            } else {
                $search_value = '';
            }

            $search_value = explode(':', $search_value);
            $search_items[$key] = end($search_value);
            $search_items[$key] = trim($search_items[$key]);
        }

        $search_items['query'] = $search_query;

        return $search_items;
    }
}
