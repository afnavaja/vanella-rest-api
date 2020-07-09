<?php

namespace Vanella\Validators;

class Required extends Validator
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
        $this->ruleName = 'isRequired';
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
        if (empty($value)) {
            $message = !is_null($customMessage) ? $customMessage : 'The ' . $field . ' field is required.';
            $this->message = [
                'rule' => $this->ruleName,
                'message' => $message,
            ];
        }
    }
}
