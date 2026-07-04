<?php

declare(strict_types=1);

namespace AEFS\Services;

use AEFS\Models\ShiftRegistration;

final class NotificationService
{
    public function __construct(
        private readonly MailService $mailService,
        private readonly DashboardNotificationService $dashboardNotificationService,
    ) {
    }

    /**
     * ==========================================================
     * SHIFTS
     * ==========================================================
     */

    public function shiftApproved(
        ShiftRegistration $registration
    ): void {

        $this->mailService->sendShiftApprovedMail(
            $registration
        );

        $this->dashboardNotificationService->create(
            $registration->lidId,
            'shift_approved',
            'Je inschrijving voor een shift werd goedgekeurd.'
        );
    }

    public function shiftRejected(
        ShiftRegistration $registration
    ): void {

        $this->mailService->sendShiftRejectedMail(
            $registration
        );

        $this->dashboardNotificationService->create(
            $registration->lidId,
            'shift_rejected',
            'Je inschrijving voor een shift werd geweigerd.'
        );
    }

    public function shiftReserve(
        ShiftRegistration $registration
    ): void {

        $this->mailService->sendShiftReserveMail(
            $registration
        );

        $this->dashboardNotificationService->create(
            $registration->lidId,
            'shift_reserve',
            'Je staat momenteel op de reservelijst.'
        );
    }

    public function reservePromoted(
        ShiftRegistration $registration
    ): void {

        $this->mailService->sendReservePromotedMail(
            $registration
        );

        $this->dashboardNotificationService->create(
            $registration->lidId,
            'reserve_promoted',
            'Je bent doorgeschoven van reserve naar bevestigd.'
        );
    }

    public function shiftCancelled(
        ShiftRegistration $registration
    ): void {

        $this->mailService->sendShiftCancelledMail(
            $registration
        );

        $this->dashboardNotificationService->create(
            $registration->lidId,
            'shift_cancelled',
            'Een shift werd geannuleerd.'
        );
    }

    public function shiftCancelledByVolunteer(
        ShiftRegistration $registration,
        ?string $reason = null
    ): void {

        $this->mailService->notifyAdminsVolunteerCancelled(
            $registration,
            $reason
        );
    }

    public function shiftCancelledByAdmin(
        ShiftRegistration $registration,
        ?string $reason = null
    ): void {

        $this->mailService->notifyVolunteerCancelledByAdmin(
            $registration,
            $reason
        );

        $this->dashboardNotificationService->create(
            $registration->lidId,
            'shift_cancelled_admin',
            'Je werd uitgeschreven voor een shift.'
        );
    }

    /**
     * ==========================================================
     * EVENTS
     * ==========================================================
     */

    public function eventCreated(
        int $eventId
    ): void {

        // later
    }

    public function eventUpdated(
        int $eventId
    ): void {

        // later
    }

    public function eventCancelled(
        int $eventId
    ): void {

        // later
    }

    /**
     * ==========================================================
     * MEMBERS
     * ==========================================================
     */

    public function memberApproved(
        int $memberId
    ): void {

        // later
    }

    public function memberRejected(
        int $memberId
    ): void {

        // later
    }

    /**
     * ==========================================================
     * PAYMENTS
     * ==========================================================
     */

    public function paymentReceived(
        int $paymentId
    ): void {

        // later
    }

    /**
     * ==========================================================
     * MAILINGS
     * ==========================================================
     */

    public function mailingFinished(
        int $mailingId
    ): void {

        // later
    }
}