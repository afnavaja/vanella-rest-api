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
     * @param string $field
     * @param string $value
     * @param string $customMessage
     *
     * @return array
     */
    public function handle($field, $value, $customMessage = null)
    {
        if (strlen($value) < $this->min && !empty($value)) {
            $message = !is_null($customMessage) ? $customMessage : 'This ' . $field . ' field should only be ' . $this->min . ' characters minimum.';
            $this->message = [
                'rule' => $this->ruleName,
                'message' => $message,
            ];
        }

        return [];
    }
}
