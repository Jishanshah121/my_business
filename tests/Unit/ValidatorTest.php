<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Exceptions\ValidationException;
use App\Validators\Validator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ValidatorTest extends TestCase
{
    // ------------------------------------------------------------ GSTIN

    /**
     * The checksum is what catches a mistyped GSTIN before it reaches an
     * invoice — format alone accepts a wrong digit happily.
     */
    #[DataProvider('gstinProvider')]
    public function testGstinValidation(string $gstin, bool $expected, string $why): void
    {
        self::assertSame($expected, Validator::isValidGstin($gstin), $why);
    }

    public static function gstinProvider(): array
    {
        return [
            'valid Maharashtra'      => ['27AAPFU0939F1ZV', true,  'known-good published example'],
            'valid, lowercase input' => ['27aapfu0939f1zv', true,  'input is normalised before checking'],
            'valid Karnataka'        => ['29AAGCB7383J1Z4', true,  'check digit computed for this base'],
            'wrong check digit'      => ['27AAPFU0939F1ZX', false, 'single wrong character must fail'],
            'unknown state code'     => ['99AAPFU0939F1ZV', false, '99 is not an Indian state code'],
            'non-numeric state'      => ['ZZAAPFU0939F1ZV', false, 'first two characters must be digits'],
            'too short'              => ['27AAPFU0939F1Z',  false, 'must be exactly 15 characters'],
            'too long'               => ['27AAPFU0939F1ZVX', false, 'must be exactly 15 characters'],
            'missing Z at pos 14'    => ['27AAPFU0939F1YV', false, 'the 14th character is always Z'],
            'empty'                  => ['',                false, 'empty is not valid'],
            'spaces are trimmed'     => [' 27AAPFU0939F1ZV ', true, 'surrounding whitespace is ignored'],
        ];
    }

    public function testStateCodeIsExtractedFromAValidGstinOnly(): void
    {
        self::assertSame('27', Validator::stateCodeFromGstin('27AAPFU0939F1ZV'));
        self::assertNull(Validator::stateCodeFromGstin('27AAPFU0939F1ZX'));
    }

    // ------------------------------------------------------------ mobile

    #[DataProvider('mobileProvider')]
    public function testIndianMobileValidation(string $input, bool $expected): void
    {
        self::assertSame($expected, Validator::isValidIndianMobile($input));
    }

    public static function mobileProvider(): array
    {
        return [
            ['9876543210', true],
            ['919876543210', true],
            ['+91 98765 43210', true],
            ['6123456789', true],       // 6-series is valid
            ['5876543210', false],      // must start 6-9
            ['987654321', false],       // 9 digits
            ['98765432101', false],     // 11 digits
            ['abcdefghij', false],
            ['', false],
        ];
    }

    public function testMobileNumbersAreNormalisedToStorageFormat(): void
    {
        foreach (['9876543210', '+91 98765 43210', '91-98765-43210'] as $input) {
            self::assertSame('919876543210', Validator::normaliseIndianMobile($input));
        }
    }

    // -------------------------------------------------------- rule engine

    public function testRequiredAndEmailRulesProduceMessages(): void
    {
        $validator = Validator::make(
            ['email' => 'not-an-email', 'first_name' => ''],
            ['email' => 'required|email', 'first_name' => 'required']
        );

        self::assertTrue($validator->fails());
        self::assertArrayHasKey('email', $validator->errors());
        self::assertArrayHasKey('first_name', $validator->errors());
    }

    public function testNullableSkipsOtherRulesWhenEmpty(): void
    {
        $validator = Validator::make(['phone' => ''], ['phone' => 'nullable|phone']);

        self::assertTrue($validator->passes(), 'an empty optional field must not fail its format rule');
    }

    public function testNullableStillValidatesWhenPresent(): void
    {
        $validator = Validator::make(['phone' => '12345'], ['phone' => 'nullable|phone']);

        self::assertTrue($validator->fails());
    }

    public function testPasswordRuleRequiresLengthAndVariety(): void
    {
        $weak = ['short1', 'allletterspassword', '1234567890123'];
        foreach ($weak as $password) {
            $validator = Validator::make(['password' => $password], ['password' => 'password']);
            self::assertTrue($validator->fails(), "[{$password}] should be rejected");
        }

        $validator = Validator::make(['password' => 'CorrectHorse42'], ['password' => 'password']);
        self::assertTrue($validator->passes());
    }

    public function testConfirmedRuleComparesAgainstTheConfirmationField(): void
    {
        $mismatch = Validator::make(
            ['password' => 'CorrectHorse42', 'password_confirmation' => 'Different99'],
            ['password' => 'confirmed']
        );
        self::assertTrue($mismatch->fails());

        $match = Validator::make(
            ['password' => 'CorrectHorse42', 'password_confirmation' => 'CorrectHorse42'],
            ['password' => 'confirmed']
        );
        self::assertTrue($match->passes());
    }

    public function testPincodeAndPanRules(): void
    {
        self::assertTrue(Validator::make(['p' => '400001'], ['p' => 'pincode'])->passes());
        self::assertTrue(Validator::make(['p' => '000001'], ['p' => 'pincode'])->fails(), 'cannot start with 0');
        self::assertTrue(Validator::make(['p' => '40001'], ['p' => 'pincode'])->fails());

        self::assertTrue(Validator::make(['p' => 'ABCDE1234F'], ['p' => 'pan'])->passes());
        self::assertTrue(Validator::make(['p' => 'ABCD1234F'], ['p' => 'pan'])->fails());
    }

    /**
     * The exception carries the input back to the form, but must never carry
     * a password with it.
     */
    public function testValidationExceptionOmitsSecretsFromOldInput(): void
    {
        $validator = Validator::make(
            ['email' => 'bad', 'password' => 'CorrectHorse42', 'reset_token' => 'abc123'],
            ['email' => 'required|email']
        );

        try {
            $validator->validate();
            self::fail('expected a ValidationException');
        } catch (ValidationException $e) {
            $old = $e->old();
            self::assertArrayHasKey('email', $old);
            self::assertArrayNotHasKey('password', $old, 'passwords must never be echoed back');
            self::assertArrayNotHasKey('reset_token', $old, 'tokens must never be echoed back');
        }
    }

    public function testUnknownRuleFailsLoudlyAsAProgrammingError(): void
    {
        $this->expectException(\RuntimeException::class);
        Validator::make(['x' => 'y'], ['x' => 'no_such_rule'])->passes();
    }
}
