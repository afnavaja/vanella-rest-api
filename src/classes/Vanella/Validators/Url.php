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
     * @param array $args
     *
     * @return array
     */
    public function handle($args = [])
    {
        if (!filter_var($args['value'], FILTER_VALIDATE_URL) && !empty($args['value'])) {
            $message = isset($args['customMessage']) ? $args['customMessage'] : 'This ' . $args['field'] . ' field is not a valid URL.';
            $this->message = [
                'rule' => $this->ruleName,
                'message' => $message,
            ];
        }
    }
}
