<?php

namespace App\Exceptions\Cards;

use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

class CardNotUpdatedException extends HttpException
{
    public function __construct(Throwable $previous = null)
    {
        $code = 500;
        $message = $previous->getMessage() ?? 'Ocorreu um erro inesperado ao atualizar o cartão.';
        parent::__construct($code, $message, $previous);
    }
}
