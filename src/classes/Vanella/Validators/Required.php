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
     * @param array $args
     *
     * @return array
     */
    public function handle($args = [])
    {
        if (empty($args['value'])) {
            $message = isset($args['customMessage']) ? $args['customMessage'] : 'The ' . $args['field'] . ' field is required.';
            $this->message = [
                'rule' => $this->ruleName,
                'message' => $message,
            ];
        }
    }
}
