<?php

namespace Vanella\Validators;

class Date extends Validator
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
        $this->ruleName = 'isDate';
        parent::__construct($args);
    }

    /**
     * Checks if the date is valid "Y-m-d" format
     *
     * @param array $args
     *
     * @return array
     */
    public function handle($args = [])
    {
        if (!empty($args['value'])) {
            $newDate = explode('-', $args['value']);

            $year = isset($newDate[0]) ? $newDate[0] : null;
            $month = isset($newDate[1]) ? $newDate[1] : null;
            $day = isset($newDate[2]) ? $newDate[2] : null;

            if (!checkdate($month, $day, $year)) {
                $message = isset($args['customMessage']) ? $args['customMessage'] : 'This ' . $args['field'] . ' field is not a valid date.';
                $this->message = [
                    'rule' => $this->ruleName,
                    'message' => $message,
                ];
            }
        }
    }
}
