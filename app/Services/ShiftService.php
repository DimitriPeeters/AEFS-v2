<?php

declare(strict_types=1);

namespace App\Services;

use AEFS\Core\Auth;
use AEFS\Core\Database;
use App\Models\Event;
use App\Models\Shift;
use App\Models\ShiftRegistration;
use App\Models\ShiftType;
use App\Repositories\EventRepository;
use App\Repositories\ShiftRegistrationRepository;
use App\Repositories\ShiftRepository;
use App\Repositories\ShiftTypeRepository;
use App\Validators\ShiftRegistrationValidator;
use App\Validators\ShiftValidator;
use DateTimeImmutable;
use DomainException;
use InvalidArgumentException;
use RuntimeException;

final class ShiftService
{
    public function __construct(
        private readonly Database $database,
        private readonly ShiftRepository $shiftRepository,
        private readonly ShiftRegistrationRepository $registrationRepository,
        private readonly ShiftTypeRepository $typeRepository,
        private readonly EventRepository $eventRepository,
        private readonly ShiftValidator $shiftValidator,
        private readonly ShiftRegistrationValidator $registrationValidator,
        private readonly AuditLogService $auditLog
    ) {
    }

    /**
     * @return Shift[]
     */
    public function allForAdministration(): array
    {
        return $this->shiftRepository->allForAdministration();
    }

    /**
     * @return Shift[]
     */
    public function visibleToMembers(): array
    {
        return $this->shiftRepository->visibleToMembers();
    }

    /**
     * @return Shift[]
     */
    public function findByEvent(
        int $eventId,
        bool $includeCancelled = true
    ): array {
        if ($eventId <= 0) {
            return [];
        }

        return $this->shiftRepository->findByEvent(
            $eventId,
            $includeCancelled
        );
    }

    public function find(int $id): ?Shift
    {
        if ($id <= 0) {
            return null;
        }

        return $this->shiftRepository->find($id);
    }

    public function findVisibleToMembers(int $id): ?Shift
    {
        if ($id <= 0) {
            return null;
        }

        return $this->shiftRepository->findVisibleToMembers($id);
    }

    /**
     * @return ShiftType[]
     */
    public function allTypes(): array
    {
        return $this->typeRepository->all();
    }

    /**
     * @return ShiftType[]
     */
    public function activeTypes(): array
    {
        $this->typeRepository->ensureDefault();

        return $this->typeRepository->active();
    }

    /**
     * @return ShiftRegistration[]
     */
    public function registrationsForShift(int $shiftId): array
    {
        if ($shiftId <= 0) {
            return [];
        }

        return $this->registrationRepository->findByShift($shiftId);
    }

    /**
     * @return ShiftRegistration[]
     */
    public function registrationsForMember(int $memberId): array
    {
        if ($memberId <= 0) {
            return [];
        }

        return $this->registrationRepository->findByMember($memberId);
    }

    /**
     * @return ShiftRegistration[]
     */
    public function pendingRegistrations(): array
    {
        return $this->registrationRepository->findPending();
    }

    public function findRegistration(
        int $registrationId
    ): ?ShiftRegistration {
        if ($registrationId <= 0) {
            return null;
        }

        return $this->registrationRepository->find($registrationId);
    }

    public function findMemberRegistration(
        int $shiftId,
        int $memberId
    ): ?ShiftRegistration {
        if ($shiftId <= 0 || $memberId <= 0) {
            return null;
        }

        return $this->registrationRepository->findByShiftAndMember(
            $shiftId,
            $memberId
        );
    }

    /**
     * @param array<string, mixed> $data
     */
    public function create(array $data): int
    {
        $data['status'] = Shift::STATUS_ACTIEF;

        $event = $this->requireEvent(
            (int) ($data['event_id'] ?? 0)
        );

        $type = $this->requireType(
            (int) ($data['type_id'] ?? 0)
        );

        if (!$type->isActief()) {
            throw new DomainException(
                'Het gekozen shifttype is niet actief.'
            );
        }

        $this->shiftValidator->validateForEvent(
            $data,
            $event
        );

        return $this->database->transaction(
            function () use ($data): int {
                $id = $this->shiftRepository->create($data);

                $this->auditLog->created(
                    entity: 'shift',
                    id: $id,
                    userId: Auth::id(),
                    values: $data
                );

                return $id;
            }
        );
    }

    /**
     * @param array<string, mixed> $data
     */
    public function update(
        int $id,
        array $data
    ): void {
        if ($id <= 0) {
            throw new InvalidArgumentException(
                'Ongeldige shift.'
            );
        }

        $this->database->transaction(
            function () use ($id, $data): void {
                $shift = $this->shiftRepository->lockForUpdate($id);

                if ($shift === null) {
                    throw new InvalidArgumentException(
                        'Shift niet gevonden.'
                    );
                }

                $requestedStatus = (string) (
                    $data['status'] ?? $shift->status
                );

                if ($requestedStatus !== $shift->status) {
                    throw new DomainException(
                        'Wijzig de status niet via het formulier. Gebruik de afzonderlijke annuleeractie.'
                    );
                }

                $data['status'] = $shift->status;

                $event = $this->requireEvent(
                    (int) ($data['event_id'] ?? 0)
                );

                $type = $this->requireType(
                    (int) ($data['type_id'] ?? 0)
                );

                if (
                    !$type->isActief()
                    && $type->typeId !== $shift->typeId
                ) {
                    throw new DomainException(
                        'Het gekozen shifttype is niet actief.'
                    );
                }

                $this->shiftValidator->validateForEvent(
                    $data,
                    $event
                );

                $registrationCount = $this->shiftRepository
                    ->countRegistrations($id);

                if (
                    $registrationCount > 0
                    && (int) $data['event_id'] !== $shift->eventId
                ) {
                    throw new DomainException(
                        'Een shift met inschrijvingen kan niet naar een ander evenement worden verplaatst.'
                    );
                }

                $confirmedCount = $this->shiftRepository
                    ->countConfirmed($id);

                if ((int) $data['max_personen'] < $confirmedCount) {
                    throw new DomainException(
                        sprintf(
                            'De capaciteit kan niet lager zijn dan het huidige aantal van %d bevestigde vrijwilligers.',
                            $confirmedCount
                        )
                    );
                }

                $this->shiftRepository->update(
                    $id,
                    $data
                );

                $this->auditLog->updated(
                    entity: 'shift',
                    id: $id,
                    userId: Auth::id(),
                    oldValues: $shift->toAuditArray(),
                    newValues: $data
                );
            }
        );
    }

    public function cancelShift(
        int $id,
        ?string $reason = null
    ): int {
        if ($id <= 0) {
            throw new InvalidArgumentException(
                'Ongeldige shift.'
            );
        }

        $reason = $this->normalizeReason(
            $reason,
            'Shift geannuleerd door een administrator.'
        );

        $this->registrationValidator
            ->validateCancellationReason($reason);

        return $this->database->transaction(
            function () use ($id, $reason): int {
                $shift = $this->shiftRepository->lockForUpdate($id);

                if ($shift === null) {
                    throw new InvalidArgumentException(
                        'Shift niet gevonden.'
                    );
                }

                if ($shift->isGeannuleerd()) {
                    throw new DomainException(
                        'Deze shift is al geannuleerd.'
                    );
                }

                $userId = $this->requireAuthenticatedUserId();

                $registrations = $this->registrationRepository
                    ->findByShift($id);

                $activeRegistrations = array_values(
                    array_filter(
                        $registrations,
                        static fn(
                            ShiftRegistration $registration
                        ): bool => $registration->isActief()
                    )
                );

                $this->shiftRepository->setStatus(
                    $id,
                    Shift::STATUS_GEANNULEERD
                );

                $this->registrationRepository->cancelActiveByShift(
                    shiftId: $id,
                    cancelledBy: $userId,
                    reason: $reason
                );

                $newShiftValues = $shift->toAuditArray();
                $newShiftValues['status'] = Shift::STATUS_GEANNULEERD;

                $this->auditLog->updated(
                    entity: 'shift',
                    id: $id,
                    userId: $userId,
                    oldValues: $shift->toAuditArray(),
                    newValues: $newShiftValues
                );

                foreach ($activeRegistrations as $registration) {
                    $updated = $this->registrationRepository->find(
                        $registration->inschrijvingId
                    );

                    if ($updated === null) {
                        continue;
                    }

                    $this->auditLog->updated(
                        entity: 'shift_registration',
                        id: $registration->inschrijvingId,
                        userId: $userId,
                        oldValues: $registration->toAuditArray(),
                        newValues: $updated->toAuditArray()
                    );
                }

                return count($activeRegistrations);
            }
        );
    }

    public function delete(int $id): void
    {
        if ($id <= 0) {
            throw new InvalidArgumentException(
                'Ongeldige shift.'
            );
        }

        $this->database->transaction(
            function () use ($id): void {
                $shift = $this->shiftRepository->lockForUpdate($id);

                if ($shift === null) {
                    throw new InvalidArgumentException(
                        'Shift niet gevonden.'
                    );
                }

                if ($this->shiftRepository->countRegistrations($id) > 0) {
                    throw new DomainException(
                        'Een shift met inschrijvingen kan niet worden verwijderd. Annuleer de shift in plaats daarvan.'
                    );
                }

                $this->shiftRepository->delete($id);

                $this->auditLog->deleted(
                    entity: 'shift',
                    id: $id,
                    userId: Auth::id(),
                    oldValues: $shift->toAuditArray()
                );
            }
        );
    }

    public function register(
        int $shiftId,
        int $memberId,
        ?string $comment = null
    ): int {
        if ($shiftId <= 0 || $memberId <= 0) {
            throw new InvalidArgumentException(
                'Ongeldige shiftinschrijving.'
            );
        }

        $comment = $this->normalizeNullableString($comment);

        $this->registrationValidator->validateComment($comment);

        return $this->database->transaction(
            function () use (
                $shiftId,
                $memberId,
                $comment
            ): int {
                $shift = $this->shiftRepository->lockForUpdate($shiftId);

                if ($shift === null) {
                    throw new InvalidArgumentException(
                        'Shift niet gevonden.'
                    );
                }

                if (!$shift->isActief()) {
                    throw new DomainException(
                        'Voor deze shift kan niet meer worden ingeschreven.'
                    );
                }

                if ($shift->eventStatus !== Event::STATUS_PUBLISHED) {
                    throw new DomainException(
                        'Dit evenement staat niet open voor shiftinschrijvingen.'
                    );
                }

                if (
                    new DateTimeImmutable($shift->startOp)
                    <= new DateTimeImmutable()
                ) {
                    throw new DomainException(
                        'Deze shift is al gestart.'
                    );
                }

                if (
                    !$this->registrationRepository
                        ->memberHasEventRegistration(
                            $shift->eventId,
                            $memberId
                        )
                ) {
                    throw new DomainException(
                        'Je moet eerst voor het evenement ingeschreven zijn voordat je een shift kunt kiezen.'
                    );
                }

                $existing = $this->registrationRepository
                    ->findByShiftAndMember(
                        $shiftId,
                        $memberId
                    );

                if ($existing !== null && $existing->isActief()) {
                    throw new DomainException(
                        'Je bent al voor deze shift ingeschreven.'
                    );
                }

                $registrationId = $this->registrationRepository->submit(
                    shiftId: $shiftId,
                    memberId: $memberId,
                    comment: $comment
                );

                $registration = $this->registrationRepository->find(
                    $registrationId
                );

                if ($registration === null) {
                    throw new RuntimeException(
                        'De shiftinschrijving kon niet worden geladen.'
                    );
                }

                if ($existing === null) {
                    $this->auditLog->created(
                        entity: 'shift_registration',
                        id: $registrationId,
                        userId: Auth::id(),
                        values: $registration->toAuditArray()
                    );
                } else {
                    $this->auditLog->updated(
                        entity: 'shift_registration',
                        id: $registrationId,
                        userId: Auth::id(),
                        oldValues: $existing->toAuditArray(),
                        newValues: $registration->toAuditArray()
                    );
                }

                return $registrationId;
            }
        );
    }

    public function approve(int $registrationId): void
    {
        $this->changeDecision(
            registrationId: $registrationId,
            targetStatus: ShiftRegistration::STATUS_BEVESTIGD
        );
    }

    public function reserve(int $registrationId): void
    {
        $this->changeDecision(
            registrationId: $registrationId,
            targetStatus: ShiftRegistration::STATUS_RESERVE
        );
    }

    public function reject(int $registrationId): void
    {
        $this->changeDecision(
            registrationId: $registrationId,
            targetStatus: ShiftRegistration::STATUS_GEWEIGERD
        );
    }

    public function cancelByAdmin(
        int $registrationId,
        ?string $reason = null
    ): void {
        $reason = $this->normalizeReason(
            $reason,
            'Inschrijving geannuleerd door een administrator.'
        );

        $this->registrationValidator
            ->validateCancellationReason($reason);

        $this->cancelRegistration(
            registrationId: $registrationId,
            expectedMemberId: null,
            reason: $reason,
            enforceMemberDeadline: false
        );
    }

    public function cancelByVolunteer(
        int $registrationId,
        int $memberId,
        ?string $reason = null
    ): void {
        if ($memberId <= 0) {
            throw new InvalidArgumentException(
                'Ongeldig lid.'
            );
        }

        $reason = $this->normalizeReason(
            $reason,
            'Geannuleerd door het lid.'
        );

        $this->registrationValidator
            ->validateCancellationReason($reason);

        $this->cancelRegistration(
            registrationId: $registrationId,
            expectedMemberId: $memberId,
            reason: $reason,
            enforceMemberDeadline: true
        );
    }

    public function setPresence(
        int $registrationId,
        bool $present
    ): void {
        if ($registrationId <= 0) {
            throw new InvalidArgumentException(
                'Ongeldige shiftinschrijving.'
            );
        }

        $this->database->transaction(
            function () use ($registrationId, $present): void {
                $registration = $this->registrationRepository
                    ->findForUpdate($registrationId);

                if ($registration === null) {
                    throw new InvalidArgumentException(
                        'Shiftinschrijving niet gevonden.'
                    );
                }

                if (!$registration->isBevestigd()) {
                    throw new DomainException(
                        'Aanwezigheid kan alleen voor bevestigde vrijwilligers worden geregistreerd.'
                    );
                }

                if ($registration->aanwezig === $present) {
                    return;
                }

                $this->registrationRepository->setPresence(
                    $registrationId,
                    $present
                );

                $updated = $this->registrationRepository->find(
                    $registrationId
                );

                if ($updated === null) {
                    throw new RuntimeException(
                        'De gewijzigde shiftinschrijving kon niet worden geladen.'
                    );
                }

                $this->auditLog->updated(
                    entity: 'shift_registration',
                    id: $registrationId,
                    userId: Auth::id(),
                    oldValues: $registration->toAuditArray(),
                    newValues: $updated->toAuditArray()
                );
            }
        );
    }

    private function changeDecision(
        int $registrationId,
        string $targetStatus
    ): void {
        if ($registrationId <= 0) {
            throw new InvalidArgumentException(
                'Ongeldige shiftinschrijving.'
            );
        }

        if (
            !in_array(
                $targetStatus,
                [
                    ShiftRegistration::STATUS_BEVESTIGD,
                    ShiftRegistration::STATUS_RESERVE,
                    ShiftRegistration::STATUS_GEWEIGERD,
                ],
                true
            )
        ) {
            throw new InvalidArgumentException(
                'Ongeldige beslissing.'
            );
        }

        $registrationSnapshot = $this->registrationRepository->find(
            $registrationId
        );

        if ($registrationSnapshot === null) {
            throw new InvalidArgumentException(
                'Shiftinschrijving niet gevonden.'
            );
        }

        $this->database->transaction(
            function () use (
                $registrationId,
                $targetStatus,
                $registrationSnapshot
            ): void {
                $shift = $this->shiftRepository->lockForUpdate(
                    $registrationSnapshot->shiftId
                );

                if ($shift === null) {
                    throw new InvalidArgumentException(
                        'Shift niet gevonden.'
                    );
                }

                $registration = $this->registrationRepository
                    ->findForUpdate($registrationId);

                if ($registration === null) {
                    throw new InvalidArgumentException(
                        'Shiftinschrijving niet gevonden.'
                    );
                }

                if ($registration->shiftId !== $shift->shiftId) {
                    throw new RuntimeException(
                        'De shiftinschrijving werd gelijktijdig gewijzigd.'
                    );
                }

                if ($registration->status === $targetStatus) {
                    return;
                }

                if (
                    !in_array(
                        $registration->status,
                        [
                            ShiftRegistration::STATUS_WACHTEND,
                            ShiftRegistration::STATUS_RESERVE,
                        ],
                        true
                    )
                ) {
                    throw new DomainException(
                        'Deze shiftinschrijving kan niet meer op deze manier worden beoordeeld.'
                    );
                }

                if (
                    $targetStatus === ShiftRegistration::STATUS_BEVESTIGD
                    && $this->registrationRepository->countByStatus(
                        $shift->shiftId,
                        ShiftRegistration::STATUS_BEVESTIGD
                    ) >= $shift->maxPersonen
                ) {
                    throw new DomainException(
                        'Deze shift is volzet. Plaats het lid op de reservelijst.'
                    );
                }

                $userId = $this->requireAuthenticatedUserId();

                $this->registrationRepository->setDecision(
                    id: $registrationId,
                    status: $targetStatus,
                    approvedBy: $userId
                );

                $updated = $this->registrationRepository->find(
                    $registrationId
                );

                if ($updated === null) {
                    throw new RuntimeException(
                        'De gewijzigde shiftinschrijving kon niet worden geladen.'
                    );
                }

                $this->auditLog->updated(
                    entity: 'shift_registration',
                    id: $registrationId,
                    userId: $userId,
                    oldValues: $registration->toAuditArray(),
                    newValues: $updated->toAuditArray()
                );
            }
        );
    }

    private function cancelRegistration(
        int $registrationId,
        ?int $expectedMemberId,
        string $reason,
        bool $enforceMemberDeadline
    ): void {
        if ($registrationId <= 0) {
            throw new InvalidArgumentException(
                'Ongeldige shiftinschrijving.'
            );
        }

        $registrationSnapshot = $this->registrationRepository->find(
            $registrationId
        );

        if ($registrationSnapshot === null) {
            throw new InvalidArgumentException(
                'Shiftinschrijving niet gevonden.'
            );
        }

        $this->database->transaction(
            function () use (
                $registrationId,
                $registrationSnapshot,
                $expectedMemberId,
                $reason,
                $enforceMemberDeadline
            ): void {
                $shift = $this->shiftRepository->lockForUpdate(
                    $registrationSnapshot->shiftId
                );

                if ($shift === null) {
                    throw new InvalidArgumentException(
                        'Shift niet gevonden.'
                    );
                }

                $registration = $this->registrationRepository
                    ->findForUpdate($registrationId);

                if ($registration === null) {
                    throw new InvalidArgumentException(
                        'Shiftinschrijving niet gevonden.'
                    );
                }

                if (
                    $expectedMemberId !== null
                    && $registration->lidId !== $expectedMemberId
                ) {
                    throw new DomainException(
                        'Je kunt alleen je eigen shiftinschrijving annuleren.'
                    );
                }

                if (!$registration->isActief()) {
                    throw new DomainException(
                        'Deze shiftinschrijving is niet meer actief.'
                    );
                }

                if (
                    $enforceMemberDeadline
                    && !$shift->magLidZelfAnnuleren()
                ) {
                    throw new DomainException(
                        'Vanaf veertien dagen voor de start van het evenement kan alleen een administrator deze inschrijving annuleren.'
                    );
                }

                $userId = $this->requireAuthenticatedUserId();

                $this->registrationRepository->cancel(
                    id: $registrationId,
                    cancelledBy: $userId,
                    reason: $reason
                );

                $updated = $this->registrationRepository->find(
                    $registrationId
                );

                if ($updated === null) {
                    throw new RuntimeException(
                        'De geannuleerde shiftinschrijving kon niet worden geladen.'
                    );
                }

                $this->auditLog->updated(
                    entity: 'shift_registration',
                    id: $registrationId,
                    userId: $userId,
                    oldValues: $registration->toAuditArray(),
                    newValues: $updated->toAuditArray()
                );
            }
        );
    }

    private function requireEvent(int $eventId): Event
    {
        if ($eventId <= 0) {
            throw new InvalidArgumentException(
                'Kies een geldig evenement.'
            );
        }

        return $this->eventRepository->find($eventId)
            ?? throw new InvalidArgumentException(
                'Evenement niet gevonden.'
            );
    }

    private function requireType(int $typeId): ShiftType
    {
        if ($typeId <= 0) {
            throw new InvalidArgumentException(
                'Kies een geldig shifttype.'
            );
        }

        return $this->typeRepository->find($typeId)
            ?? throw new InvalidArgumentException(
                'Shifttype niet gevonden.'
            );
    }

    private function requireAuthenticatedUserId(): int
    {
        $userId = Auth::id();

        if ($userId === null || $userId <= 0) {
            throw new RuntimeException(
                'Er is geen aangemelde gebruiker beschikbaar.'
            );
        }

        return $userId;
    }

    private function normalizeReason(
        ?string $reason,
        string $default
    ): string {
        return $this->normalizeNullableString($reason)
            ?? $default;
    }

    private function normalizeNullableString(
        ?string $value
    ): ?string {
        if ($value === null) {
            return null;
        }

        $value = trim($value);

        return $value !== ''
            ? $value
            : null;
    }
}