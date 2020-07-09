<?php

namespace Vanella\Validators;

class Email extends Validator
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
        $this->ruleName = 'isEmail';
        parent::__construct($args);
    }

    /**
     * Checks if the value is empty
     *
     * @param string $field
     * @param string $value
     * @param string $customMessage
     *
     * @return array
     */
    public function handle($field, $value, $customMessage = null)
    {
        if (!filter_var($value, FILTER_VALIDATE_EMAIL) && !empty($value)) {
            $message = !is_null($customMessage) ? $customMessage : 'This ' . $field . ' field is not a valid email.';
            $this->message = [
                'rule' => $this->ruleName,
                'message' => $message,
            ];
        }
    }
}
