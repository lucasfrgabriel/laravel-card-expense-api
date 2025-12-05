<?php

namespace App\Enums;

enum CardStatusEnum:string
{
    case Ativo = 'ativo';
    case Bloqueado = 'bloqueado';
    case Cancelado = 'cancelado';
}
