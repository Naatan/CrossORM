<?php

namespace CrossORM\Security;

use \CrossORM\Exceptions;

class Validation {

    /**
     * Validate a value with a given set of rules
     *
     * @param   string          $label
     * @param   mixed           $value
     * @param   string          $rules
     *
     * @return  void                            Throws Exceptions\Validation on failure
     */
    static public function run($label,$value,$rules,$ref = NULL)
    {
        $rules = static::parse_rules($rules);

        foreach ($rules AS $rule)
        {
            $args = array($value);

            // If a rule has arguments it will be formatted in an array
            // the first entry being the rule, the second the arguments
            if (is_array($rule))
            {
                $args = array_merge($args,$rule[1]);
                $rule = $rule[0];
            }

            // Check what validation method to use
            if ($ref != NULL AND method_exists($ref,'_validate_'.$rule))
            {
                $method = array($ref,'_validate_'.$rule);
            }
                else if (method_exists(get_class(),'_validate_'.$rule))
            {
                $method = array(get_class(),'_validate_'.$rule);
            }
                else
            {
                // Throw exception if rule does not exist
                throw new Exceptions\Validation('Validation rule does not exist: ' . (string) $rule);
            }

            // Throw exception if rule method returned false
            if ( ! call_user_func_array($method,$args))
            {
                array_shift($args);
                throw new Exceptions\Validation(array(
                    'label'     => $label,
                    'rule'      => $rule,
                    'args'      => $args,
                    'input'     => $value,
                    'message'   => 'Could not validate field "' . $label . '" with rule "' . $rule . '"'
                ));
            }
        }
    }

    /**
     * Validate if value is textual, pretty much just checks if it's a string
     *
     * @param   string          $value
     *
     * @return  bool
     */
    static public function _validate_text($value)
    {
        return is_string($value);
    }

    /**
     * Validate if value is an integer
     *
     * @param   int         $value
     *
     * @return  bool
     */
    static public function _validate_int($value)
    {
        return is_numeric($value);
    }

    /**
     * Validate if value is numeric
     *
     * @param   int|float|string            $value
     *
     * @return  bool
     */
    static public function _validate_numeric($value)
    {
        return is_numeric($value);
    }

    /**
     * Validate if value does not exceed max length
     *
     * @param   string          $value
     * @param   int         $length
     *
     * @return  bool
     */
    static public function _validate_maxlength($value,$length=0)
    {
        return strlen($value) <= $length;
    }

    /**
     * Validate if value is alphanumerical
     *
     * @param   string          $value
     *
     * @return  bool
     */
    static public function _validate_alphanumeric($value)
    {
        return preg_match('/^[a-z0-9]+$/i',$value);
    }

    /**
     * Validate if value is alphabetical
     *
     * @param   string          $value
     *
     * @return  bool
     */
    static public function _validate_alphabetical($value)
    {
        return preg_match('/^[a-z]+$/i',$value);
    }

    /**
     * Parse rules in string format into an array
     *
     * @param   string          $rules
     *
     * @return  array
     */
    static private function parse_rules($rules)
    {

        if (substr($rules,0,1)=='[' AND $rules = json_decode($rules))
        {
            return $rules;
        }

        $rules = preg_replace('/([a-z0-9-_]+)/i','"$1"',$rules);
        $rules = preg_replace('/(\"[a-z0-9-_]+\")(\[.*?\])/i','[$1, $2]',$rules);

        if ( ! $rules = json_decode('[' . $rules . ']'))
        {
            throw new Exceptions\Validation('Invalid rule format');
        }
            else
        {
            return $rules;
        }

    }

}
