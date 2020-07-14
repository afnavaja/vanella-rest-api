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
     * @param array $args
     *
     * @return array
     */
    public function handle($args = [])
    {
        if (!filter_var($args['value'], FILTER_VALIDATE_EMAIL) && !empty($args['value'])) {
            $message = isset($args['customMessage']) ? $args['customMessage'] : 'This ' . $args['field'] . ' field is not a valid email.';
            $this->message = [
                'rule' => $this->ruleName,
                'message' => $message,
            ];
        }
    }
}
