<?php

namespace App\Exceptions;

use Exception;
use Throwable;

class CardNotUpdatedException extends Exception
{
    public function __construct(Throwable $previous = null)
    {
        $code = 500;
        $message = 'Ocorreu um erro inesperado ao atualizar o cartão.';
        parent::__construct($code, $message, $previous);
    }
}
