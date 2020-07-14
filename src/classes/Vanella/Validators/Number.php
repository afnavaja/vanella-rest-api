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
     * @param array $args
     *
     * @return array
     */
    public function handle($args = [])
    {
        if (!is_numeric($args['value']) && !empty($args['value'])) {
            $message = isset($args['customMessage']) ? $args['customMessage'] : 'This ' . $args['field'] . ' field should be a number.';
            $this->message = [
                'rule' => $this->ruleName,
                'message' => $message,
            ];
        }
    }
}
