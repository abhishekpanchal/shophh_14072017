<?php

namespace Unirgy\DropshipBatch\Model;

use Magento\Email\Model\Template\Filter;
use Magento\Framework\DataObject;
use Magento\Framework\Filter\Template\Tokenizer\Variable;
use Magento\Framework\Profiler;

class TemplateFilter extends Filter
{
    protected function _getVariable($value, $default='{no_value_defined}')
    {
        Profiler::start("email_template_proccessing_variables");
        $tokenizer = new Variable();
        $tokenizer->setString($value);
        $stackVars = $tokenizer->tokenize();
        $result = $default;
        $last = 0;
        for($i = 0; $i < count($stackVars); $i ++) {
            if ($i == 0 && isset($this->_templateVars[$stackVars[$i]['name']])) {
                // Getting of template value
                $stackVars[$i]['variable'] =& $this->_templateVars[$stackVars[$i]['name']];
            } elseif (isset($stackVars[$i-1]['variable'])
                && $stackVars[$i-1]['variable'] instanceof DataObject
            ) {
                // If object calling methods or getting properties
                if($stackVars[$i]['type'] == 'property') {
                    $caller = "get" . uc_words($stackVars[$i]['name'], '');
                    if(is_callable([$stackVars[$i-1]['variable'], $caller])) {
                        // If specified getter for this property
                        $stackVars[$i]['variable'] = $stackVars[$i-1]['variable']->$caller();
                    } else {
                        $stackVars[$i]['variable'] = $stackVars[$i-1]['variable']
                                                        ->getData($stackVars[$i]['name']);
                    }
                } else if ($stackVars[$i]['type'] == 'method') {
                    // Calling of object method
                    if (is_callable([$stackVars[$i-1]['variable'], $stackVars[$i]['name']]) || substr($stackVars[$i]['name'],0,3) == 'get') {
                        $stackVars[$i]['variable'] = call_user_func_arrayfunc([$stackVars[$i-1]['variable'],
                                                                                $stackVars[$i]['name']],
                                                                          $stackVars[$i]['args']);
                    }

                }
                $last = $i;
            } elseif (isset($stackVars[$i-1]['variable'])
                && is_object($stackVars[$i-1]['variable'])
            ) {
                if($stackVars[$i]['type'] == 'property' && isset($stackVars[$i-1]['variable']->{$stackVars[$i]['name']})) {
                    $stackVars[$i]['variable'] = $stackVars[$i-1]['variable']->{$stackVars[$i]['name']};
                } else if ($stackVars[$i]['type'] == 'method') {
                    // Calling of object method
                    if (is_callable([$stackVars[$i-1]['variable'], $stackVars[$i]['name']])) {
                        $stackVars[$i]['variable'] = call_user_func_arrayfunc(
                            [$stackVars[$i-1]['variable'],$stackVars[$i]['name']],
                            $stackVars[$i]['args']
                        );
                    }

                }
                $last = $i;
            }
        }

        if(isset($stackVars[$last]['variable'])) {
            // If value for construction exists set it
            $result = $stackVars[$last]['variable'];
        }
        Profiler::stop("email_template_proccessing_variables");
        return $result;
    }
    public function varDirective($construction)
    {
        $value = parent::varDirective($construction);
        $value = str_replace('"', '""', $value);
        return $value;
    }
}