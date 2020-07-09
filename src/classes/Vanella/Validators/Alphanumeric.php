<?php

namespace Vanella\Validators;

class Alphanumeric extends Validator
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
        $this->ruleName = 'isAlphanumeric';
        parent::__construct($args);
    }

    /**
     * Checks if the value is alphanumeric
     *
     * @param string $field
     * @param string $value
     * @param string $customMessage
     *
     * @return array
     */
    public function handle($field, $value, $customMessage = null)
    {
        if (!ctype_alnum($value) && !empty($value)) {
            $message = !is_null($customMessage) ? $customMessage : 'This ' . $field . ' field should only contain alphanumeric values.';
            $this->message = [
                'rule' => $this->ruleName,
                'message' => $message,
            ];
        }

        return [];
    }
}
