<?php

namespace Fruitcake\B2BEssentials\Test\Unit\Plugin;

use Fruitcake\B2BEssentials\Helper\Data;
use Fruitcake\B2BEssentials\Plugin\DisableCustomerEmailNotification;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\EmailNotification;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class DisableCustomerEmailNotificationTest extends TestCase
{
    private function plugin(array $config): DisableCustomerEmailNotification
    {
        $helper = $this->createMock(Data::class);
        $helper->method('getConfig')->willReturnCallback(fn (string $path) => $config[$path] ?? '0');

        return new DisableCustomerEmailNotification($helper);
    }

    public function testNewAccountIsSentWhenNotDisabled(): void
    {
        $called = false;
        $customer = $this->createMock(CustomerInterface::class);

        $this->plugin([])->aroundNewAccount(
            $this->createMock(EmailNotification::class),
            function (...$args) use (&$called, $customer) {
                $called = true;
                $this->assertSame([$customer, EmailNotification::NEW_ACCOUNT_EMAIL_REGISTERED], $args);
            },
            $customer,
            EmailNotification::NEW_ACCOUNT_EMAIL_REGISTERED
        );

        $this->assertTrue($called);
    }

    public static function newAccountProvider(): array
    {
        // args after $proceed, expected to send
        return [
            'default type' => [[], false],
            'registered' => [[EmailNotification::NEW_ACCOUNT_EMAIL_REGISTERED], false],
            'registered no password' => [[EmailNotification::NEW_ACCOUNT_EMAIL_REGISTERED_NO_PASSWORD], false],
            'confirmed' => [[EmailNotification::NEW_ACCOUNT_EMAIL_CONFIRMED], false],
            'confirmation link' => [[EmailNotification::NEW_ACCOUNT_EMAIL_CONFIRMATION, 'https://example.com'], true],
        ];
    }

    #[DataProvider('newAccountProvider')]
    public function testNewAccountWhenDisabled(array $args, bool $expectSend): void
    {
        $called = false;

        $this->plugin(['emails/disable_registration' => '1'])->aroundNewAccount(
            $this->createMock(EmailNotification::class),
            function () use (&$called) {
                $called = true;
            },
            $this->createMock(CustomerInterface::class),
            ...$args
        );

        $this->assertSame($expectSend, $called);
    }

    public static function credentialsProvider(): array
    {
        // config, isPasswordChanged, expected to send
        return [
            'nothing disabled, email changed' => [[], false, true],
            'nothing disabled, password changed' => [[], true, true],
            'email disabled, email changed' => [['emails/disable_email_changed' => '1'], false, false],
            'email disabled, password changed' => [['emails/disable_email_changed' => '1'], true, true],
            'password disabled, password changed' => [['emails/disable_password_changed' => '1'], true, false],
            'password disabled, email changed' => [['emails/disable_password_changed' => '1'], false, true],
        ];
    }

    #[DataProvider('credentialsProvider')]
    public function testCredentialsChanged(array $config, bool $isPasswordChanged, bool $expectSend): void
    {
        $called = false;
        $customer = $this->createMock(CustomerInterface::class);

        $this->plugin($config)->aroundCredentialsChanged(
            $this->createMock(EmailNotification::class),
            function ($savedCustomer, $origEmail, $passwordChanged) use (&$called, $customer, $isPasswordChanged) {
                $called = true;
                $this->assertSame($customer, $savedCustomer);
                $this->assertSame('old@example.com', $origEmail);
                $this->assertSame($isPasswordChanged, $passwordChanged);
            },
            $customer,
            'old@example.com',
            $isPasswordChanged
        );

        $this->assertSame($expectSend, $called);
    }
}
