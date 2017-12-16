<?php
namespace App\Entity;

class SMS
{
    protected $number;
    protected $message;

    /**
     * Funcition getNumber
     *
     * @return void
     */
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * Funcition setNumber
     *
     * @param mixed $number
     * @return void
     */
    public function setNumber($number)
    {
        $this->number = $number;
    }

    /**
     * Funcition getMessage
     *
     * @return void
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Funcition setMessage
     *
     * @param mixed $message
     * @return void
     */
    public function setMessage($message)
    {
        $this->message = $message;
    }
}