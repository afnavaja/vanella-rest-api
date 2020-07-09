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
     * @param string $field
     * @param string $value
     * @param string $customMessage
     *
     * @return array
     */
    public function handle($field, $value, $customMessage = null)
    {
        if (!empty($value)) {
            $newDate = explode('-', $value);

            $year = isset($newDate[0]) ? $newDate[0] : null;
            $month = isset($newDate[1]) ? $newDate[1] : null;
            $day = isset($newDate[2]) ? $newDate[2] : null;

            if (!checkdate($month, $day, $year)) {
                $message = !is_null($customMessage) ? $customMessage : 'This ' . $field . ' field is not a valid date.';
                $this->message = [
                    'rule' => $this->ruleName,
                    'message' => $message,
                ];
            }
        }
    }
}
