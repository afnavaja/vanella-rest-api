<?php

namespace Vanella\Validators;

class Number extends Validator
{
    /**
     * Class construct
     *
     * @param array $args
     *
     * @return void
     */
    public function __construct($args = [])
    {
        parent::__construct($args);
        $this->ruleName = 'isNumber';
    }

    /**
     * Checks if the value is a number
     *
     * @param string $field
     * @param string $value
     * @param string $customMessage
     *
     * @return array
     */
    public function handle($field, $value, $customMessage = null)
    {
        if (!is_numeric($value) && !empty($value)) {
            $message = !is_null($customMessage) ? $customMessage : 'This ' . $field . ' field should be a number.';
            $this->message = [
                'rule' => $this->ruleName,
                'message' => $message,
            ];
        }
    }
}
