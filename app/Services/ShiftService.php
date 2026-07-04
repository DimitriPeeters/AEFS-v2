<?php

declare(strict_types=1);

namespace AEFS\Services;

use AEFS\Models\Shift;
use AEFS\Models\ShiftRegistration;
use AEFS\Repositories\EventRepository;
use AEFS\Repositories\ShiftRegistrationRepository;
use AEFS\Repositories\ShiftRepository;
use DateInterval;
use DateTime;
use RuntimeException;
use Throwable;

final class ShiftService
{
    public function __construct(
        private readonly ShiftRepository $shiftRepository,
        private readonly ShiftRegistrationRepository $registrationRepository,
        private readonly EventRepository $eventRepository,
        private readonly NotificationService $notificationService,
        private readonly AuditService $auditService,
    ) {
    }

    /**
     * =========================================================================
     * SHIFTS
     * =========================================================================
     */

    public function createShift(
        int $eventId,
        string $functie,
        string $datum,
        string $starttijd,
        string $eindtijd,
        int $maxVrijwilligers
    ): int {

        if ($functie === '') {
            $functie = 'Steward';
        }

        if ($maxVrijwilligers < 1) {
            throw new RuntimeException(
                'Maximum vrijwilligers moet minstens 1 zijn.'
            );
        }

        if ($starttijd >= $eindtijd) {
            throw new RuntimeException(
                'Eindtijd moet later liggen dan de starttijd.'
            );
        }

        $id = $this->shiftRepository->createShift(
            $eventId,
            $functie,
            $datum,
            $starttijd,
            $eindtijd,
            $maxVrijwilligers
        );

        $this->auditService->log(
            'shift',
            $id,
            'create'
        );

        return $id;
    }

    public function updateShift(
        int $shiftId,
        string $functie,
        string $datum,
        string $starttijd,
        string $eindtijd,
        int $maxVrijwilligers
    ): void {

        $shift = $this->shiftRepository->find($shiftId);

        if (!$shift instanceof Shift) {
            throw new RuntimeException(
                'Shift niet gevonden.'
            );
        }

        if ($starttijd >= $eindtijd) {
            throw new RuntimeException(
                'Eindtijd moet later liggen dan de starttijd.'
            );
        }

        if ($maxVrijwilligers < 1) {
            throw new RuntimeException(
                'Ongeldig maximum.'
            );
        }

        $this->shiftRepository->updateShift(
            $shiftId,
            $functie,
            $datum,
            $starttijd,
            $eindtijd,
            $maxVrijwilligers
        );

        $this->auditService->log(
            'shift',
            $shiftId,
            'update'
        );
    }

    public function cancelShift(
        int $shiftId,
        int $adminId
    ): void {

        $shift = $this->shiftRepository->find($shiftId);

        if (!$shift instanceof Shift) {
            throw new RuntimeException(
                'Shift niet gevonden.'
            );
        }

        $this->shiftRepository->cancel($shiftId);

        foreach (
            $this->registrationRepository->findByShift($shiftId)
            as $registration
        ) {

            $this->notificationService
                ->shiftCancelled(
                    $registration
                );
        }

        $this->auditService->log(
            'shift',
            $shiftId,
            'cancel'
        );
    }

    public function deleteShift(
        int $shiftId
    ): void {

        $shift = $this->shiftRepository->find($shiftId);

        if (!$shift instanceof Shift) {
            throw new RuntimeException(
                'Shift niet gevonden.'
            );
        }

        $this->shiftRepository->deleteShift(
            $shiftId
        );

        $this->auditService->log(
            'shift',
            $shiftId,
            'delete'
        );
    }

    /**
     * =========================================================================
     * REGISTRATIONS
     * =========================================================================
     */

    public function registerVolunteer(
        int $shiftId,
        int $memberId
    ): int {

        if (
            $this->registrationRepository
                ->existsForMember(
                    $shiftId,
                    $memberId
                )
        ) {

            throw new RuntimeException(
                'Je bent reeds ingeschreven.'
            );
        }

        $shift = $this->shiftRepository
            ->find($shiftId);

        if (!$shift instanceof Shift) {
            throw new RuntimeException(
                'Shift niet gevonden.'
            );
        }

        if (!$shift->isActief()) {
            throw new RuntimeException(
                'Shift is niet actief.'
            );
        }

        $status = ShiftRegistration::STATUS_WACHTEND;

        if ($this->shiftRepository->isFull($shiftId)) {
            $status = ShiftRegistration::STATUS_RESERVE;
        }

        $registrationId =
            $this->registrationRepository
                ->createRegistration(
                    $shiftId,
                    $memberId,
                    $status
                );

        $this->auditService->log(
            'shift_registration',
            $registrationId,
            'create'
        );

        return $registrationId;
    }

        public function approveRegistration(
        int $registrationId,
        int $approvedBy
    ): void {

        $registration = $this->registrationRepository->find($registrationId);

        if (!$registration instanceof ShiftRegistration) {
            throw new RuntimeException(
                'Inschrijving niet gevonden.'
            );
        }

        $shift = $this->shiftRepository->find($registration->shiftId);

        if (!$shift instanceof Shift) {
            throw new RuntimeException(
                'Shift niet gevonden.'
            );
        }

        $this->shiftRepository->beginTransaction();

        try {

            if ($this->shiftRepository->isFull($shift->id)) {

                $this->registrationRepository->reserve(
                    $registrationId,
                    $approvedBy
                );

                $this->notificationService->shiftReserve(
                    $registration
                );

            } else {

                $this->registrationRepository->approve(
                    $registrationId,
                    $approvedBy
                );

                $this->notificationService->shiftApproved(
                    $registration
                );
            }

            $this->auditService->log(
                'shift_registration',
                $registrationId,
                'approve'
            );

            $this->shiftRepository->commit();

        } catch (Throwable $e) {

            $this->shiftRepository->rollBack();

            throw $e;
        }
    }

    public function rejectRegistration(
        int $registrationId,
        int $approvedBy
    ): void {

        $registration = $this->registrationRepository->find($registrationId);

        if (!$registration instanceof ShiftRegistration) {
            throw new RuntimeException(
                'Inschrijving niet gevonden.'
            );
        }

        $this->registrationRepository->reject(
            $registrationId,
            $approvedBy
        );

        $this->notificationService->shiftRejected(
            $registration
        );

        $this->auditService->log(
            'shift_registration',
            $registrationId,
            'reject'
        );
    }

    public function reserveRegistration(
        int $registrationId,
        int $approvedBy
    ): void {

        $registration = $this->registrationRepository->find($registrationId);

        if (!$registration instanceof ShiftRegistration) {
            throw new RuntimeException(
                'Inschrijving niet gevonden.'
            );
        }

        $this->registrationRepository->reserve(
            $registrationId,
            $approvedBy
        );

        $this->notificationService->shiftReserve(
            $registration
        );

        $this->auditService->log(
            'shift_registration',
            $registrationId,
            'reserve'
        );
    }

    public function cancelRegistration(
        int $registrationId,
        int $userId,
        bool $isAdmin = false,
        ?string $reason = null
    ): void {

        $registration = $this->registrationRepository->find($registrationId);

        if (!$registration instanceof ShiftRegistration) {
            throw new RuntimeException(
                'Inschrijving niet gevonden.'
            );
        }

        $shift = $this->shiftRepository->find($registration->shiftId);

        if (!$shift instanceof Shift) {
            throw new RuntimeException(
                'Shift niet gevonden.'
            );
        }

        if (!$isAdmin) {

            $eventDate = new DateTime($shift->shiftDatum);
            $limitDate = (clone $eventDate)->sub(
                new DateInterval('P14D')
            );

            if (new DateTime() >= $limitDate) {
                throw new RuntimeException(
                    'Vanaf 14 dagen voor het evenement kan enkel een administrator of eventmanager een annulatie uitvoeren.'
                );
            }
        }

        $this->shiftRepository->beginTransaction();

        try {

            $this->registrationRepository->cancel(
                $registrationId,
                $userId,
                $reason
            );

            if ($isAdmin) {

                $this->notificationService
                    ->shiftCancelledByAdmin(
                        $registration,
                        $reason
                    );

            } else {

                $this->notificationService
                    ->shiftCancelledByVolunteer(
                        $registration,
                        $reason
                    );
            }

            $this->promoteReserveList(
                $registration->shiftId,
                $userId
            );

            $this->auditService->log(
                'shift_registration',
                $registrationId,
                'cancel'
            );

            $this->shiftRepository->commit();

        } catch (Throwable $e) {

            $this->shiftRepository->rollBack();

            throw $e;
        }
    }

    private function promoteReserveList(
        int $shiftId,
        int $approvedBy
    ): void {

        if ($this->shiftRepository->isFull($shiftId)) {
            return;
        }

        $reserve = $this->registrationRepository
            ->findNextReserve($shiftId);

        if (!$reserve instanceof ShiftRegistration) {
            return;
        }

        $this->registrationRepository->approve(
            $reserve->id,
            $approvedBy
        );

        $this->notificationService
            ->reservePromoted($reserve);

        $this->auditService->log(
            'shift_registration',
            $reserve->id,
            'reserve_promoted'
        );
    }

        /**
     * =========================================================================
     * OPVRAGEN
     * =========================================================================
     */

    /**
     * @return Shift[]
     */
    public function findByEvent(
        int $eventId,
        bool $activeOnly = false
    ): array {

        if ($activeOnly) {
            return $this->shiftRepository->findActiveByEvent($eventId);
        }

        return $this->shiftRepository->findByEvent($eventId);
    }

    public function findShift(
        int $shiftId
    ): ?Shift {

        return $this->shiftRepository
            ->findWithStatistics($shiftId);
    }

    /**
     * @return ShiftRegistration[]
     */
    public function findRegistrations(
        int $shiftId
    ): array {

        return $this->registrationRepository
            ->findByShift($shiftId);
    }

    /**
     * @return ShiftRegistration[]
     */
    public function confirmedRegistrations(
        int $shiftId
    ): array {

        return $this->registrationRepository
            ->findConfirmed($shiftId);
    }

    /**
     * @return ShiftRegistration[]
     */
    public function waitingRegistrations(
        int $shiftId
    ): array {

        return $this->registrationRepository
            ->findWaiting($shiftId);
    }

    /**
     * @return ShiftRegistration[]
     */
    public function reserveRegistrations(
        int $shiftId
    ): array {

        return $this->registrationRepository
            ->findReserve($shiftId);
    }

    public function statistics(
        int $shiftId
    ): array {

        $shift = $this->shiftRepository
            ->findWithStatistics($shiftId);

        if (!$shift instanceof Shift) {
            throw new RuntimeException(
                'Shift niet gevonden.'
            );
        }

        return [
            'max_vrijwilligers' => $shift->maxVrijwilligers,
            'bevestigd' => $shift->aantalBevestigd,
            'wachtend' => $shift->aantalWachtend,
            'reserve' => $shift->aantalReserve,
            'beschikbaar' => $shift->beschikbarePlaatsen(),
            'volzet' => $shift->isVolzet(),
        ];
    }

    public function canVolunteerCancel(
        int $shiftId
    ): bool {

        $shift = $this->shiftRepository->find($shiftId);

        if (!$shift instanceof Shift) {
            return false;
        }

        $eventDate = new DateTime($shift->shiftDatum);
        $limitDate = (clone $eventDate)->sub(
            new DateInterval('P14D')
        );

        return new DateTime() < $limitDate;
    }

    public function isShiftFull(
        int $shiftId
    ): bool {

        return $this->shiftRepository
            ->isFull($shiftId);
    }

    public function availablePlaces(
        int $shiftId
    ): int {

        $shift = $this->shiftRepository
            ->findWithStatistics($shiftId);

        if (!$shift instanceof Shift) {
            return 0;
        }

        return $shift->beschikbarePlaatsen();
    }

    public function countConfirmed(
        int $shiftId
    ): int {

        return $this->shiftRepository
            ->countConfirmed($shiftId);
    }

    public function countWaiting(
        int $shiftId
    ): int {

        return $this->shiftRepository
            ->countWaiting($shiftId);
    }

    public function countReserve(
        int $shiftId
    ): int {

        return $this->shiftRepository
            ->countReserve($shiftId);
    }
}