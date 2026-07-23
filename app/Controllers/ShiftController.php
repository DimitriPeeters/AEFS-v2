<?php

declare(strict_types=1);

namespace App\Controllers;

use AEFS\Core\Http\Request;
use AEFS\Core\Http\Response;
use AEFS\Core\Session;
use AEFS\Core\View\ViewFactory;
use App\Repositories\EventRepository;
use App\Repositories\ShiftRegistrationRepository;
use App\Repositories\ShiftRepository;
use App\Repositories\ShiftTypeRepository;
use App\Services\ShiftService;
use Throwable;

final class ShiftController extends BaseController
{
    public function __construct(
        ViewFactory $views,
        Request $request,
        private readonly ShiftService $service,
        private readonly ShiftRepository $shiftRepository,
        private readonly ShiftTypeRepository $shiftTypeRepository,
        private readonly ShiftRegistrationRepository $registrationRepository,
        private readonly EventRepository $eventRepository
    ) {
        parent::__construct(
            $views,
            $request
        );
    }

    public function index(): Response
    {
        return $this->view(
            'shifts.index',
            [
                'title' => 'Shiftplanning',
                'events' => $this->eventRepository->all(),
            ]
        );
    }

    public function planner(int $eventId): Response
    {
        $event = $this->eventRepository->find($eventId);

        if ($event === null) {
            return $this->notFound(
                'Evenement niet gevonden.'
            );
        }

        return $this->view(
            'shifts.planner',
            [
                'title' => $event->titel,
                'event' => $event,
                'shifts' => $this->shiftRepository->byEvent($eventId),
                'shiftTypes' => $this->shiftTypeRepository->all(),
            ]
        );
    }

    public function show(int $shiftId): Response
    {
        $shift = $this->shiftRepository->find($shiftId);

        if ($shift === null) {
            return $this->notFound(
                'Shift niet gevonden.'
            );
        }

        return $this->view(
            'shifts.show',
            [
                'title' => 'Shift',
                'shift' => $shift,
                'registrations' => $this->registrationRepository->byShift(
                    $shiftId
                ),
            ]
        );
    }

    public function create(): Response
    {
        return $this->view(
            'shifts.form',
            [
                'title' => 'Nieuwe shift',
                'shiftTypes' => $this->shiftTypeRepository->all(),
            ]
        );
    }

    public function store(): Response
    {
        $input = $this->request()->all();

        Session::flash(
            '_old_input',
            $input
        );

        try {
            $id = $this->service->createShift($input);

            $this->success(
                'De shift werd succesvol aangemaakt.'
            );

            return $this->redirect(
                '/shifts/' . $id
            );
        } catch (Throwable $throwable) {
            $this->storeException(
                $throwable,
                'De shift kon niet worden aangemaakt.'
            );

            return $this->redirect(
                '/shifts/create'
            );
        }
    }

    public function update(int $shiftId): Response
    {
        $shift = $this->shiftRepository->find($shiftId);

        if ($shift === null) {
            return $this->notFound(
                'Shift niet gevonden.'
            );
        }

        $input = $this->request()->all();

        Session::flash(
            '_old_input',
            $input
        );

        try {
            $this->service->updateShift(
                $shiftId,
                $input
            );

            $this->success(
                'De shift werd succesvol gewijzigd.'
            );

            return $this->redirect(
                '/shifts/' . $shiftId
            );
        } catch (Throwable $throwable) {
            $this->storeException(
                $throwable,
                'De shift kon niet worden gewijzigd.'
            );

            return $this->redirect(
                '/shifts/' . $shiftId . '/edit'
            );
        }
    }

    public function delete(int $shiftId): Response
    {
        $shift = $this->shiftRepository->find($shiftId);

        if ($shift === null) {
            return $this->notFound(
                'Shift niet gevonden.'
            );
        }

        try {
            $this->service->deleteShift($shiftId);

            $this->success(
                'De shift werd succesvol verwijderd.'
            );
        } catch (Throwable $throwable) {
            $this->error(
                $throwable->getMessage()
            );
        }

        return $this->redirect('/shifts');
    }

    public function register(int $shiftId): Response
    {
        try {
            $this->service->register(
                $shiftId,
                (int) auth()->member()->lidId,
                $this->nullablePostString('opmerking_lid')
            );

            $this->success(
                'Je inschrijving werd geregistreerd en wacht op goedkeuring.'
            );
        } catch (Throwable $throwable) {
            $this->error(
                $throwable->getMessage()
            );
        }

        return $this->redirectBack(
            '/shifts/' . $shiftId
        );
    }

    public function approve(int $registrationId): Response
    {
        try {
            $this->service->approve(
                $registrationId,
                (int) auth()->user()->gebruikerId
            );

            $this->success(
                'De inschrijving werd goedgekeurd.'
            );
        } catch (Throwable $throwable) {
            $this->error(
                $throwable->getMessage()
            );
        }

        return $this->redirectBack('/shifts');
    }

    public function reserve(int $registrationId): Response
    {
        try {
            $this->service->reserve(
                $registrationId,
                (int) auth()->user()->gebruikerId
            );

            $this->success(
                'De inschrijving werd als reserve gemarkeerd.'
            );
        } catch (Throwable $throwable) {
            $this->error(
                $throwable->getMessage()
            );
        }

        return $this->redirectBack('/shifts');
    }

    public function reject(int $registrationId): Response
    {
        try {
            $this->service->reject(
                $registrationId,
                (int) auth()->user()->gebruikerId,
                $this->nullablePostString('reden')
            );

            $this->success(
                'De inschrijving werd geweigerd.'
            );
        } catch (Throwable $throwable) {
            $this->error(
                $throwable->getMessage()
            );
        }

        return $this->redirectBack('/shifts');
    }

    public function cancel(int $registrationId): Response
    {
        try {
            $user = auth()->user();
            $reason = $this->nullablePostString('reden');

            if ($user->isAdmin() || $user->isEventManager()) {
                $this->service->cancelByAdmin(
                    $registrationId,
                    (int) $user->gebruikerId,
                    $reason
                );
            } else {
                $this->service->cancelByVolunteer(
                    $registrationId,
                    (int) auth()->member()->lidId,
                    $reason
                );
            }

            $this->success(
                'De inschrijving werd geannuleerd.'
            );
        } catch (Throwable $throwable) {
            $this->error(
                $throwable->getMessage()
            );
        }

        return $this->redirectBack('/shifts');
    }

    public function lock(int $shiftId): Response
    {
        try {
            $this->service->lockShift($shiftId);

            $this->success(
                'De shift werd vergrendeld.'
            );
        } catch (Throwable $throwable) {
            $this->error(
                $throwable->getMessage()
            );
        }

        return $this->redirectBack(
            '/shifts/' . $shiftId
        );
    }

    public function unlock(int $shiftId): Response
    {
        try {
            $this->service->unlockShift($shiftId);

            $this->success(
                'De shift werd ontgrendeld.'
            );
        } catch (Throwable $throwable) {
            $this->error(
                $throwable->getMessage()
            );
        }

        return $this->redirectBack(
            '/shifts/' . $shiftId
        );
    }

    private function notFound(string $message): Response
    {
        return $this->view(
            'core::errors.404',
            [
                'message' => $message,
            ],
            404
        );
    }

    private function storeException(
        Throwable $throwable,
        string $message
    ): void {
        Session::flash(
            '_errors',
            [
                'form' => [
                    $throwable->getMessage(),
                ],
            ]
        );

        $this->error($message);
    }

    private function nullablePostString(string $key): ?string
    {
        $value = trim(
            (string) $this->post(
                $key,
                ''
            )
        );

        return $value === ''
            ? null
            : $value;
    }

    private function redirectBack(string $fallback): Response
    {
        $referer = $_SERVER['HTTP_REFERER'] ?? null;

        if (!is_string($referer) || trim($referer) === '') {
            return $this->redirect($fallback);
        }

        return $this->redirect($referer);
    }
}