<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Services\Utils;

class CardValidationTest extends TestCase
{
    public function test_valid_card_number(): void
    {
        $cardNumber = "1234567812345670";
        $result = Utils::luhnCheck($cardNumber);
        $this->assertTrue($result);
    }

    public function test_invalid_card_number(): void
    {
        $cardNumber = "1234567812345678";
        $result = Utils::luhnCheck($cardNumber);
        $this->assertFalse($result);
    }
}
