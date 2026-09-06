<?php

declare(strict_types=1);

namespace App\Services\Notification;

use App\Support\Database;
use PDO;

/**
 * Records every notification and dispatches it on the appropriate channel.
 *
 * Email is the only live channel in Phase 2. SMS and WhatsApp arrive in Phase 9
 * and plug in here without any caller changing: the caller names an event, not
 * a transport.
 */
final class NotificationDispatcher
{
    private PDO $pdo;

    public function __construct(
        private readonly Mailer $mailer,
        ?PDO $pdo = null,
    ) {
        $this->pdo = $pdo ?? Database::connection();
    }

    /**
     * @param array<string,mixed> $data Template variables.
     */
    public function email(
        string $event,
        string $recipient,
        string $subject,
        string $template,
        array $data = [],
        ?int $userId = null,
        ?string $referenceType = null,
        ?int $referenceId = null,
    ): bool {
        $notificationId = $this->record($event, 'email', $recipient, $subject, $userId, $referenceType, $referenceId);

        $sent = $this->mailer->send($recipient, $subject, $template, $data);

        $this->markResult($notificationId, $sent);

        return $sent;
    }

    private function record(
        string $event,
        string $channel,
        string $recipient,
        ?string $subject,
        ?int $userId,
        ?string $referenceType,
        ?int $referenceId,
    ): int {
        $stmt = $this->pdo->prepare(
            'INSERT INTO `notifications`
                (`user_id`, `channel`, `event`, `recipient`, `subject`, `reference_type`, `reference_id`, `status`)
             VALUES (:u, :c, :e, :r, :s, :rt, :ri, \'queued\')'
        );
        $stmt->bindValue('u', $userId, $userId === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
        $stmt->bindValue('c', $channel);
        $stmt->bindValue('e', $event);
        $stmt->bindValue('r', $recipient);
        $stmt->bindValue('s', $subject);
        $stmt->bindValue('rt', $referenceType, $referenceType === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $stmt->bindValue('ri', $referenceId, $referenceId === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
        $stmt->execute();

        return (int) $this->pdo->lastInsertId();
    }

    private function markResult(int $id, bool $sent): void
    {
        // Note: the notification body is deliberately NOT persisted. It would
        // put password-reset links in the database, which is exactly what
        // hashing the token was meant to prevent.
        $this->pdo->prepare(
            'UPDATE `notifications`
             SET `status` = :s, `attempts` = `attempts` + 1, `sent_at` = IF(:s2 = \'sent\', NOW(), NULL)
             WHERE `id` = :id'
        )->execute([
            's'  => $sent ? 'sent' : 'failed',
            's2' => $sent ? 'sent' : 'failed',
            'id' => $id,
        ]);
    }
}
