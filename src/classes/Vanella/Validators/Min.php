<?php

namespace Vanella\Validators;

class Min extends Validator
{
    protected $min;

    /**
     * Class construct
     *
     * @param array $args
     *
     * @return void
     */
    public function __construct($args = [])
    {
        $this->min = isset($args['args']['min']) ? $args['args']['min'] : 6;
        $this->ruleName = 'isMin';
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
        if (strlen($args['value']) < $this->min && !empty($args['value'])) {
            $message = isset($args['customMessage']) ? $args['customMessage'] : 'This ' . $args['field'] . ' field should only be ' . $this->min . ' characters minimum.';
            $this->message = [
                'rule' => $this->ruleName,
                'message' => $message,
            ];
        }

        return [];
    }
}
