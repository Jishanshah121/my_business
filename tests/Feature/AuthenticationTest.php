<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Repositories\UserRepository;

final class AuthenticationTest extends DatabaseTestCase
{
    private UserRepository $users;

    protected function setUp(): void
    {
        parent::setUp();
        $this->users = new UserRepository($this->pdo);
    }

    public function testPasswordsAreStoredAsArgon2idAndNeverInPlainText(): void
    {
        $password = 'CorrectHorse42';
        $id = $this->createUser(['password_hash' => password_hash($password, PASSWORD_ARGON2ID)]);

        $stored = (string) $this->pdo->query("SELECT `password_hash` FROM `users` WHERE `id` = {$id}")->fetchColumn();

        self::assertStringStartsWith('$argon2id$', $stored);
        self::assertStringNotContainsString($password, $stored);
        self::assertTrue(password_verify($password, $stored));
        self::assertFalse(password_verify('WrongPassword99', $stored));
    }

    public function testTheSamePasswordProducesDifferentHashes(): void
    {
        $a = password_hash('CorrectHorse42', PASSWORD_ARGON2ID);
        $b = password_hash('CorrectHorse42', PASSWORD_ARGON2ID);

        self::assertNotSame($a, $b, 'each hash must carry its own salt');
        self::assertTrue(password_verify('CorrectHorse42', $a));
        self::assertTrue(password_verify('CorrectHorse42', $b));
    }

    public function testUserCanBeFoundByEmailOrPhoneCaseInsensitively(): void
    {
        $id = $this->createUser(['email' => 'rohan@example.test', 'phone' => '919820011223']);

        self::assertSame($id, $this->users->findByEmail('rohan@example.test')?->id);
        self::assertSame($id, $this->users->findByEmail('ROHAN@EXAMPLE.TEST')?->id);
        self::assertSame($id, $this->users->findByIdentifier('rohan@example.test')?->id);
        self::assertSame($id, $this->users->findByIdentifier('9820011223')?->id, 'login accepts a bare mobile');
        self::assertNull($this->users->findByEmail('nobody@example.test'));
    }

    public function testEmailUniquenessIsEnforcedByTheDatabase(): void
    {
        $this->createUser(['email' => 'duplicate@example.test']);

        $this->expectException(\PDOException::class);
        $this->createUser(['email' => 'duplicate@example.test']);
    }

    public function testRolesAndPermissionsAreLoadedOntoTheUser(): void
    {
        $id = $this->createUser();
        $this->assignRole($id, 'order_manager');

        $user = $this->users->find($id);

        self::assertNotNull($user);
        self::assertTrue($user->hasRole('order_manager'));
        self::assertTrue($user->can('orders.view'));
        self::assertTrue($user->can('orders.refund'));
        self::assertFalse($user->can('products.create'), 'order managers must not be able to create products');
        self::assertFalse($user->can('settings.manage'));
    }

    public function testEmailVerificationActivatesAPendingAccount(): void
    {
        $id = $this->createUser(['status' => 'pending']);

        $before = $this->users->find($id);
        self::assertNotNull($before);
        self::assertFalse($before->hasVerifiedEmail());
        self::assertTrue($before->canAuthenticate(), 'a pending account is still usable');

        $this->users->markEmailVerified($id);

        $after = $this->users->find($id);
        self::assertNotNull($after);
        self::assertTrue($after->hasVerifiedEmail());
        self::assertSame('active', $after->status, 'verifying promotes pending to active');
    }

    public function testVerifyingDoesNotReactivateASuspendedAccount(): void
    {
        $id = $this->createUser(['status' => 'suspended']);

        $this->users->markEmailVerified($id);

        $user = $this->users->find($id);
        self::assertNotNull($user);
        self::assertSame('suspended', $user->status, 'only pending is promoted, never suspended');
        self::assertFalse($user->canAuthenticate());
    }

    public function testSoftDeletedUsersAreNotFound(): void
    {
        $id = $this->createUser(['email' => 'gone@example.test']);
        $this->pdo->exec("UPDATE `users` SET `deleted_at` = NOW() WHERE `id` = {$id}");

        self::assertNull($this->users->find($id));
        self::assertNull($this->users->findByEmail('gone@example.test'));
    }

    public function testPasswordUpdateChangesTheStoredHash(): void
    {
        $id = $this->createUser();
        $original = (string) $this->pdo->query("SELECT `password_hash` FROM `users` WHERE `id` = {$id}")->fetchColumn();

        $this->users->updatePassword($id, password_hash('BrandNewPass77', PASSWORD_ARGON2ID));

        $updated = (string) $this->pdo->query("SELECT `password_hash` FROM `users` WHERE `id` = {$id}")->fetchColumn();

        self::assertNotSame($original, $updated);
        self::assertTrue(password_verify('BrandNewPass77', $updated));
        self::assertFalse(password_verify('CorrectHorse42', $updated), 'the old password must stop working');
    }
}
