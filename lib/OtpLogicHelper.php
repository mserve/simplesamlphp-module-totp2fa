<?php
/*namespace SimpleSAML\Module\totp2fa;

use SimpleSAML\Logger;
use Webmozart\Assert\Assert;
*/

/**
 * sspmod_totp2fa_OtpLogicHelper
 *
 * provides handling of logic comparisons
 * for CheckAttribute
 *
 * heavily inspired by jwadhams/json-logic-php
 * Copyright (c) 2015 Jeremy Wadhams
 */
class sspmod_totp2fa_OtpLogicHelper
{
    public static function hasValidLogic($logic)
    {
        // $logic contains values to be checked in following form
        //
        // array(
        //     operator         required - a valid attribute
        //     attributeName    required - the attribute to work in
        //     value            optional - value to be checked for
        //     arrayMode        optional - setting how to handle array values if this is a "normal" comparison
        //                                 'any'    returns true if any of the array values fulfill condition (i.e. OR)
        //                                 'all'    return true if all of the array values fulfill condition  (i.e. AND)
        //                                 default is any
        //     settings         optional - settings to be applied (not required in here, should be handled outside)
        // )
    

        //// Check if we have required values
        if (!array_key_exists('operator', $logic) || !array_key_exists('attributeName', $logic)) {
            return false;
        }
        
        //// Check if operator is valid
        if (!array_key_exists($logic['operator'], static::getOperators())) {
            return false;
        }

        return true;
    }

    // Available operators
    // $array => attributes, $a => attribute name, $b => comparison
    private static function getOperators()
    {
        return array(
            // Attribute exists in attributes array
            'has' => function (&$array, $a, $b) {
                return array_key_exists($a, $array);
            },
            // Attribute value contains, also works on simple strings
            // only operator directly working on attributes containing arrays
            'contains' => function (&$array, $a, $b) {
                if (!array_key_exists($a, $array)) {
                    return false;
                }
                if (is_array($array[$a])) {
                    return in_array($b, $array[$a]);
                } else {
                    return (strpos($array[$a], $b) !== false);
                }
            },
            '==' => function ($a, $b) {
                return $a == $b;
            },
            '!=' => function ($a, $b) {
                return $a != $b;
            },
            '>' => function ($a, $b) {
                return $a > $b;
            },
            '>=' => function ($a, $b) {
                return $a >= $b;
            },
            '<' => function ($a, $b) {
                return $a < $b;
            },
            '<=' => function ($a, $b) {
                return $a <= $b;
            },
            '!!' => function ($a) {
                return static::truthy($a);
            },
            '!' => function ($a) {
                return ! static::truthy($a);
            },
            'and' => function () {
                foreach (func_get_args() as $a) {
                    if (! static::truthy($a)) {
                        return $a;
                    }
                }
                return $a;
            },
            'or' => function () {
                foreach (func_get_args() as $a) {
                    if (static::truthy($a)) {
                        return $a;
                    }
                }
                return $a;
            }
        );
    }

    public static function apply($logic, &$request)
    {
        // Request will be checked for its attributes,
        // passing whole reference could be used for
        // further extensions

        // Check if attributes exist
        if (!array_key_exists('Attributes', $request)) {
            return false;
        }
      
        //// Check if we have required values
        if (static::hasValidLogic($logic)) {
            // TODO: throw error?
            return false;
        }

        //// Set internal values
        $attr = $logic['attributeName'];
        $op = $logic['operator'];
        // Set parameter $b or use null if not set
        $val = array_key_exists('value', $logic) ? $logic['value'] : null;
        // Check if arrayMode is set
        $arrayMode = array_key_exists('arrayMode', $logic) && $logic['arrayMode'] == 'all' ? 'all' : 'any';

        //// Check if attribute exists
        if (!array_key_exists($attr, $request['Attributes'])) {
            if ($logic['operator'] === 'has') {
                // Ok, we wanted only to check for presence, hence return false as bool
                return false;
            } else {
                // TODO: throw error?
                return false;
            }
        }

        //// Ready to apply logic
        // Check if attribute is array or not
        $operators = static::getOperators();
        if (in_array($op, array('has', 'contains'))) {
            // these operators directly work on the array - just apply them!
            return $operators[$op]($request['Attributes'], $attr, $val);
        } elseif (is_array($request['Attributes'][$attr])) {
            // Attribute is array - check length / count
            if (count($request['Attributes'][$attr]) > 1) {
                // 'real' array - check arrayMode
                if ($arrayMode == 'all') {
                    // value is all - as soon as one operator is not truthy, return false
                    foreach ($request['Attributes'][$attr] as $a) {
                        if (!$operators[$op]($a, $val)) {
                            return false;
                        }
                    }
                    return true;
                } else {
                    // value is any - as soon as one operator is truthy, return true
                    foreach ($request['Attributes'][$attr] as $a) {
                        if ($operators[$op]($a, $val)) {
                            return true;
                        }
                    }
                    return false;
                }
            } else {
                // single value array - just use first value
                return $operators[$op]($request['Attributes'][$attr][0], $val);
            }
        } else {
            // no array - directly apply operator
            return $operators[$op]($request['Attributes'][$attr], $val);
        }
    }
    
    private static function truthy($logic)
    {
        if ($logic === "0") {
            return true;
        }
        return (bool)$logic;
    }
};
