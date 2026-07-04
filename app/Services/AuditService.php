<?php

declare(strict_types=1);

namespace AEFS\Services;

use AEFS\Core\Database;
use DateTimeImmutable;
use Throwable;

final class AuditService
{
    public function __construct(
        private readonly Database $database
    ) {
    }

    public function log(
        string $entity,
        int|string|null $entityId,
        string $action,
        ?int $userId = null,
        array $oldData = [],
        array $newData = [],
        ?string $remark = null
    ): void {

        try {

            $sql = "
                INSERT INTO audit_logs
                (
                    user_id,
                    entity,
                    entity_id,
                    action,
                    old_data,
                    new_data,
                    remark,
                    ip_address,
                    user_agent,
                    created_at
                )
                VALUES
                (
                    :user_id,
                    :entity,
                    :entity_id,
                    :action,
                    :old_data,
                    :new_data,
                    :remark,
                    :ip_address,
                    :user_agent,
                    :created_at
                )
            ";

            $stmt = $this->database
                ->prepare($sql);

            $stmt->execute([

                'user_id' => $userId,

                'entity' => $entity,

                'entity_id' => $entityId,

                'action' => $action,

                'old_data' => empty($oldData)
                    ? null
                    : json_encode(
                        $oldData,
                        JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT
                    ),

                'new_data' => empty($newData)
                    ? null
                    : json_encode(
                        $newData,
                        JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT
                    ),

                'remark' => $remark,

                'ip_address' => $this->ipAddress(),

                'user_agent' => $this->userAgent(),

                'created_at' => (new DateTimeImmutable())
                    ->format('Y-m-d H:i:s'),

            ]);

        } catch (Throwable) {

            /*
             * Audit logging mag de applicatie nooit blokkeren.
             */
        }
    }

    public function created(
        string $entity,
        int|string $id,
        ?int $userId = null,
        array $data = []
    ): void {

        $this->log(
            entity: $entity,
            entityId: $id,
            action: 'created',
            userId: $userId,
            newData: $data
        );
    }

    public function updated(
        string $entity,
        int|string $id,
        ?int $userId = null,
        array $oldData = [],
        array $newData = []
    ): void {

        $this->log(
            entity: $entity,
            entityId: $id,
            action: 'updated',
            userId: $userId,
            oldData: $oldData,
            newData: $newData
        );
    }

    public function deleted(
        string $entity,
        int|string $id,
        ?int $userId = null,
        array $oldData = []
    ): void {

        $this->log(
            entity: $entity,
            entityId: $id,
            action: 'deleted',
            userId: $userId,
            oldData: $oldData
        );
    }

    public function login(
        int $userId
    ): void {

        $this->log(
            entity: 'authentication',
            entityId: $userId,
            action: 'login',
            userId: $userId
        );
    }

    public function logout(
        int $userId
    ): void {

        $this->log(
            entity: 'authentication',
            entityId: $userId,
            action: 'logout',
            userId: $userId
        );
    }

    public function failedLogin(
        string $email
    ): void {

        $this->log(
            entity: 'authentication',
            entityId: null,
            action: 'failed_login',
            remark: $email
        );
    }

    public function custom(
        string $entity,
        int|string|null $entityId,
        string $action,
        ?string $remark = null,
        ?int $userId = null
    ): void {

        $this->log(
            entity: $entity,
            entityId: $entityId,
            action: $action,
            userId: $userId,
            remark: $remark
        );
    }

    private function ipAddress(): ?string
    {
        return $_SERVER['REMOTE_ADDR'] ?? null;
    }

    private function userAgent(): ?string
    {
        return $_SERVER['HTTP_USER_AGENT'] ?? null;
    }
}