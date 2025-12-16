<?php

namespace App\Exceptions\Users;

use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

class UserNotCreatedException extends HttpException
{
    public function __construct(Throwable $previous = null)
    {
        $code = 500;
        $message = 'Ocorreu um erro inesperado ao criar o usuário.';
        parent::__construct($code, $message, $previous);
    }
}
