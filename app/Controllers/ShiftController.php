<?php

declare(strict_types=1);

namespace AEFS\Controllers;

use AEFS\Core\Request;
use AEFS\Core\Response;
use AEFS\Core\View;
use AEFS\Repositories\EventRepository;
use AEFS\Repositories\ShiftRepository;
use AEFS\Repositories\ShiftRegistrationRepository;
use AEFS\Repositories\ShiftTypeRepository;
use AEFS\Services\ShiftService;
use Throwable;

final class ShiftController extends BaseController
{
    public function __construct(
        private ShiftService $service,
        private ShiftRepository $shiftRepository,
        private ShiftTypeRepository $shiftTypeRepository,
        private ShiftRegistrationRepository $registrationRepository,
        private EventRepository $eventRepository
    ) {
        parent::__construct();
    }

    public function index(): void
    {
        $events = $this->eventRepository->all();

        $this->view('shifts.index', [

            'title'  => 'Shiftplanning',

            'events' => $events,

        ]);
    }

    public function planner(int $eventId): void
    {
        $event = $this->eventRepository->find($eventId);

        if (!$event) {
            abort(404);
        }

        $this->view('shifts.planner', [

            'title'         => $event->titel,

            'event'         => $event,

            'shifts'        => $this->shiftRepository->byEvent($eventId),

            'shiftTypes'    => $this->shiftTypeRepository->all(),

        ]);
    }

    public function show(int $shiftId): void
    {
        $shift = $this->shiftRepository->find($shiftId);

        if (!$shift) {
            abort(404);
        }

        $this->view('shifts.show', [

            'title' => 'Shift',

            'shift' => $shift,

            'registrations' => $this->registrationRepository->byShift($shiftId),

        ]);
    }

    public function create(): void
    {
        $this->view('shifts.form', [

            'title' => 'Nieuwe shift',

            'shiftTypes' => $this->shiftTypeRepository->all(),

        ]);
    }

    public function store(Request $request): void
    {
        try {

            $id = $this->service->createShift(
                $request->all()
            );

            Response::redirect('/shifts/' . $id);

        } catch (Throwable $e) {

            $this->view('shifts.form', [

                'title' => 'Nieuwe shift',

                'errors' => [

                    $e->getMessage()

                ],

                'shiftTypes' => $this->shiftTypeRepository->all(),

            ]);

        }
    }

    public function update(int $shiftId, Request $request): void
    {
        try {

            $this->service->updateShift(
                $shiftId,
                $request->all()
            );

            Response::redirect('/shifts/' . $shiftId);

        } catch (Throwable $e) {

            $this->view('shifts.form', [

                'title' => 'Shift wijzigen',

                'errors' => [

                    $e->getMessage()

                ],

                'shift' => $this->shiftRepository->find($shiftId),

                'shiftTypes' => $this->shiftTypeRepository->all(),

            ]);

        }
    }

    public function delete(int $shiftId): void
    {
        $this->service->deleteShift($shiftId);

        Response::redirect('/shifts');
    }

    public function register(int $shiftId, Request $request): void
    {
        $this->service->register(

            $shiftId,

            auth()->member()->lidId,

            $request->post('opmerking_lid')

        );

        Response::redirectBack();
    }

    public function approve(int $registrationId): void
    {
        $this->service->approve(

            $registrationId,

            auth()->user()->gebruikerId

        );

        Response::redirectBack();
    }

    public function reserve(int $registrationId): void
    {
        $this->service->reserve(

            $registrationId,

            auth()->user()->gebruikerId

        );

        Response::redirectBack();
    }

    public function reject(int $registrationId, Request $request): void
    {
        $this->service->reject(

            $registrationId,

            auth()->user()->gebruikerId,

            $request->post('reden')

        );

        Response::redirectBack();
    }

    public function cancel(int $registrationId, Request $request): void
    {
        if (auth()->user()->isAdmin() || auth()->user()->isEventManager()) {

            $this->service->cancelByAdmin(

                $registrationId,

                auth()->user()->gebruikerId,

                $request->post('reden')

            );

        } else {

            $this->service->cancelByVolunteer(

                $registrationId,

                auth()->member()->lidId,

                $request->post('reden')

            );

        }

        Response::redirectBack();
    }

    public function lock(int $shiftId): void
    {
        $this->service->lockShift($shiftId);

        Response::redirectBack();
    }

    public function unlock(int $shiftId): void
    {
        $this->service->unlockShift($shiftId);

        Response::redirectBack();
    }
}