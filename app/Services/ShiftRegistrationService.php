<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\ShiftRegistration;
use App\Repositories\ShiftRegistrationRepository;
use App\Repositories\ShiftRepository;
use RuntimeException;

final class ShiftRegistrationService
{
    public function __construct(
        private readonly ShiftRegistrationRepository $registrations,
        private readonly ShiftRepository $shifts,
        private readonly NotificationService $notifications,
    ) {
    }

    /**
     * Vrijwilliger schrijft zichzelf in.
     */
    public function register(
        int $shiftId,
        int $memberId
    ): int {

        if ($this->registrations->exists($shiftId, $memberId)) {
            throw new RuntimeException(
                'Je bent reeds ingeschreven voor deze shift.'
            );
        }

        $shift = $this->shifts->find($shiftId);

        if ($shift === null) {
            throw new RuntimeException(
                'Shift bestaat niet.'
            );
        }

        if (!$shift->isActief()) {
            throw new RuntimeException(
                'Deze shift is niet beschikbaar.'
            );
        }

        return $this->registrations->registerVolunteer(
            $shiftId,
            $memberId
        );
    }

    /**
     * Admin keurt een inschrijving goed.
     */
    public function approve(
        int $registrationId,
        int $approvedBy
    ): void {

        $registration = $this->registrations->find($registrationId);

        if ($registration === null) {
            throw new RuntimeException(
                'Inschrijving niet gevonden.'
            );
        }

        if (
            $this->shifts->isFull(
                $registration->shiftId
            )
        ) {

            $this->registrations->reserve(
                $registrationId,
                $approvedBy
            );

            return;
        }

        $this->registrations->approve(
            $registrationId,
            $approvedBy
        );

        $this->notifications->shiftApproved(
            $registration
        );
    }

    public function reject(
        int $registrationId,
        int $approvedBy
    ): void {

        $registration = $this->registrations->find($registrationId);

        if ($registration === null) {
            throw new RuntimeException(
                'Inschrijving niet gevonden.'
            );
        }

        $this->registrations->reject(
            $registrationId,
            $approvedBy
        );

        $this->notifications->shiftRejected(
            $registration
        );
    }

    /**
     * Vrijwilliger annuleert.
     */
    public function cancelByVolunteer(
        int $registrationId,
        int $memberId,
        ?string $reason = null
    ): void {

        $registration = $this->registrations->find($registrationId);

        if ($registration === null) {
            throw new RuntimeException(
                'Inschrijving niet gevonden.'
            );
        }

        $shift = $this->shifts->find(
            $registration->shiftId
        );

        if ($shift === null) {
            throw new RuntimeException(
                'Shift niet gevonden.'
            );
        }

        if (
            strtotime($shift->shiftDatum)
            <= strtotime('+14 days')
        ) {

            throw new RuntimeException(
                'Je kan minder dan 14 dagen voor het evenement niet meer zelf annuleren.'
            );
        }

        $this->registrations->cancel(
            $registrationId,
            $memberId,
            $reason
        );

        $this->notifications->shiftCancelledByVolunteer(
            $registration,
            $reason
        );

        $this->promoteReserve(
            $registration->shiftId,
            $memberId
        );
    }

    /**
     * Admin annuleert.
     */
    public function cancelByAdmin(
        int $registrationId,
        int $adminId,
        ?string $reason = null
    ): void {

        $registration = $this->registrations->find($registrationId);

        if ($registration === null) {
            throw new RuntimeException(
                'Inschrijving niet gevonden.'
            );
        }

        $this->registrations->cancel(
            $registrationId,
            $adminId,
            $reason
        );

        $this->notifications->shiftCancelledByAdmin(
            $registration,
            $reason
        );

        $this->promoteReserve(
            $registration->shiftId,
            $adminId
        );
    }

    /**
     * Reserve automatisch doorschuiven.
     */
    private function promoteReserve(
        int $shiftId,
        int $approvedBy
    ): void {

        if ($this->shifts->isFull($shiftId)) {
            return;
        }

        $reserve = $this->registrations
            ->findNextReserve($shiftId);

        if ($reserve === null) {
            return;
        }

        $this->registrations
            ->moveReserveToConfirmed(
                $reserve->id,
                $approvedBy
            );

        $this->notifications
            ->reservePromoted($reserve);
    }

    /**
     * Admin zet iemand expliciet op reserve.
     */
    public function reserve(
        int $registrationId,
        int $approvedBy
    ): void {

        $registration = $this->registrations->find(
            $registrationId
        );

        if ($registration === null) {
            throw new RuntimeException(
                'Inschrijving niet gevonden.'
            );
        }

        $this->registrations->reserve(
            $registrationId,
            $approvedBy
        );

        $this->notifications->shiftReserve(
            $registration
        );
    }
}