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
     * @param array $args
     *
     * @return array
     */
    public function handle($args = [])
    {
        if (!ctype_alnum($args['value']) && !empty($args['value'])) {
            $message = isset($args['customMessage']) ? $args['customMessage'] : 'This ' . $args['field'] . ' field should only contain alphanumeric values.';
            $this->message = [
                'rule' => $this->ruleName,
                'message' => $message,
            ];
        }

        return [];
    }
}
