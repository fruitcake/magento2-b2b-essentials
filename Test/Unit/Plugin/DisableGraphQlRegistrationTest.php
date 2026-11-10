<?php

namespace Fruitcake\B2BEssentials\Test\Unit\Plugin;

use Fruitcake\B2BEssentials\Plugin\DisableGraphQlRegistration;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Model\Registration;
use Magento\Framework\Exception\LocalizedException;
use PHPUnit\Framework\TestCase;

class DisableGraphQlRegistrationTest extends TestCase
{
    private function plugin(bool $allowed): DisableGraphQlRegistration
    {
        $registration = $this->createMock(Registration::class);
        $registration->method('isAllowed')->willReturn($allowed);

        return new DisableGraphQlRegistration($registration);
    }

    public function testAllowsWhenRegistrationAllowed(): void
    {
        $this->assertNull(
            $this->plugin(true)->beforeCreateAccount($this->createMock(AccountManagementInterface::class))
        );
    }

    public function testThrowsWhenRegistrationDisabled(): void
    {
        $this->expectException(LocalizedException::class);

        $this->plugin(false)->beforeCreateAccount($this->createMock(AccountManagementInterface::class));
    }
}
