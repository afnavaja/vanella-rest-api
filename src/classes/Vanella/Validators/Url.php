<?php

namespace Vanella\Validators;

class Url extends Validator
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
        $this->ruleName = 'isUrl';
        parent::__construct($args);
    }

    /**
     * Checks if the value is URL
     *
     * @param string $field
     * @param string $value
     * @param string $customMessage
     *
     * @return array
     */
    public function handle($field, $value, $customMessage = null)
    {
        if (!filter_var($value, FILTER_VALIDATE_URL) && !empty($value)) {
            $message = !is_null($customMessage) ? $customMessage : 'This ' . $field . ' field is not a valid URL.';
            $this->message = [
                'rule' => $this->ruleName,
                'message' => $message,
            ];
        }
    }
}
