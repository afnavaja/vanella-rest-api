<?php

namespace Vanella\Validators;

class Max extends Validator
{
    protected $max;

    /**
     * Class construct
     *
     * @param array $args
     *
     * @return void
     */
    public function __construct($args = [])
    {
        $this->max = isset($args['args']['max']) ? $args['args']['max'] : 12;
        $this->ruleName = 'isMax';
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
        if (strlen($args['value']) > $this->max && !empty($args['value'])) {
            $message = isset($args['customMessage']) ? $args['customMessage'] : 'This ' . $args['field'] . ' field should only be ' . $this->max . ' characters max.';
            $this->message = [
                'rule' => $this->ruleName,
                'message' => $message,
            ];
        }

        return [];
    }
}
