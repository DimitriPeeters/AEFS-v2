<?php

declare(strict_types=1);

namespace App\Controllers;

use AEFS\Core\Auth;
use AEFS\Core\Http\JsonResponse;
use AEFS\Core\Http\Request;
use AEFS\Core\Http\Response;
use AEFS\Core\Session;
use AEFS\Core\View\Helper\CsrfHelper;
use AEFS\Core\View\ViewFactory;
use App\Http\Requests\ShiftRegistrationRequest;
use App\Http\Requests\ShiftRequest;
use App\Repositories\EventRepository;
use App\Services\EventAccessService;
use App\Services\EventCompanionService;
use App\Services\ShiftService;
use App\Services\SettingsService;
use DomainException;
use InvalidArgumentException;
use RuntimeException;
use Throwable;

final class ShiftController extends BaseController
{
    public function __construct(
        ViewFactory $views,
        Request $request,
        private readonly ShiftService $service,
        private readonly EventCompanionService $companions,
        private readonly EventAccessService $access,
        private readonly SettingsService $settings,
        private readonly EventRepository $eventRepository,
        private readonly CsrfHelper $csrf
    ) {
        parent::__construct(
            $views,
            $request
        );
    }

    public function index(): Response
    {
        $isAdmin = Auth::isAdmin();
        $canManage = $this->access->hasManagementAccess();
        $memberId = Auth::memberId();
        $events = $isAdmin
            ? $this->eventRepository->allForAdministration()
            : $this->managedEvents();
        $shifts = $isAdmin
            ? $this->service->allForAdministration()
            : [];

        if (!$isAdmin) {
            foreach ($events as $event) {
                $shifts = [
                    ...$shifts,
                    ...$this->service->findByEvent($event->eventId, true),
                ];
            }
        }

        return $this->view(
            'shifts.index',
            [
                'title' => 'Shiftplanning',
                'isAdmin' => $canManage,
                'events' => $events,
                'shifts' => $shifts,
                'memberRegistrations' => $memberId !== null
                    ? $this->service->registrationsForMember(
                        $memberId
                    )
                    : [],
                'pendingRegistrations' => $isAdmin
                    ? $this->service->pendingRegistrations()
                    : [],
            ]
        );
    }

    public function planner(): Response
    {
        $eventId = $this->routeId('eventId');

        if (!$this->access->canManage($eventId)) {
            return $this->forbidden();
        }
        $event = $this->eventRepository->find($eventId);

        if ($event === null) {
            return $this->notFound(
                'Evenement niet gevonden.'
            );
        }

        return $this->view(
            'shifts.planner',
            [
                'title' => 'Shiftplanning · ' . $event->titel,
                'event' => $event,
                'shifts' => $this->service->findByEvent(
                    $eventId
                ),
                'shiftTypes' => $this->service->allTypes(),
            ]
        );
    }

    public function show(): Response
    {
        $shiftId = $this->routeId();
        $memberId = Auth::memberId();
        $shift = $this->service->find($shiftId);
        $canManage = $shift !== null
            && $this->access->canManage($shift->eventId);
        $memberRegistration = !$canManage && $memberId !== null
            ? $this->service->findMemberRegistration(
                $shiftId,
                $memberId
            )
            : null;

        $shift = $canManage
            ? $shift
            : ($memberRegistration !== null
                ? $this->service->find($shiftId)
                : null);

        if ($shift === null) {
            return $this->notFound(
                'Shift niet gevonden.'
            );
        }

        $eligible = $canManage
            ? $this->service->eligibleEventRegistrationsForShift($shiftId)
            : [];
        $assignedIds = [];
        if ($canManage) {
            foreach ($this->service->registrationsForShift($shiftId) as $assignment) {
                if ($assignment->isActief()) {
                    $assignedIds[] = $assignment->lidId;
                }
            }
        }
        $chain = array_values(array_filter(array_map(
            'intval',
            explode(',', (string) $this->request()->query->get('companion_chain', ''))
        ), static fn(int $id): bool => $id > 0));
        $chain = array_slice($chain, 0, 50);
        $companionCandidates = [];
        while ($canManage && $chain !== []) {
            $sourceId = end($chain);
            if (!in_array($sourceId, $assignedIds, true)) {
                array_pop($chain);
                continue;
            }
            $selected = $this->companions->selectedIds($shift->eventId, $sourceId);
            $companionCandidates = array_values(array_filter(
                $eligible,
                static fn($registration): bool => in_array($registration->lidId, $selected, true)
                    && !in_array($registration->lidId, $chain, true)
            ));
            if ($companionCandidates !== []) {
                break;
            }
            array_pop($chain);
        }

        return $this->view(
            'shifts.show',
            [
                'title' => $shift->displayNaam(),
                'shift' => $shift,
                'isAdmin' => $canManage,
                'registrations' => $canManage
                    ? $this->service->registrationsForShift(
                        $shiftId
                    )
                    : [],
                'memberRegistration' => $memberRegistration,
                'eligibleEventRegistrations' => $eligible,
                'companionChain' => $chain,
                'companionCandidates' => $companionCandidates,
            ]
        );
    }

    public function create(): Response
    {
        if (!$this->access->hasManagementAccess()) {
            return $this->forbidden();
        }

        $selectedEventId = (int) $this->request()
            ->query
            ->get(
                'event_id',
                0
            );

        if (
            $selectedEventId > 0
            && !$this->access->canManage($selectedEventId)
        ) {
            return $this->forbidden();
        }

        return $this->view(
            'shifts.create',
            [
                'title' => 'Nieuwe shift',
                'events' => Auth::isAdmin()
                    ? $this->eventRepository->allForAdministration()
                    : $this->managedEvents(),
                'shiftTypes' => $this->service
                    ->activeTypes(),
                'selectedEventId' => $selectedEventId,
                'defaultShiftCompensation' => $this->settings
                    ->defaultShiftCompensation(),
                'groupSupplement' => $this->settings
                    ->groupSupplement(),
            ]
        );
    }

    public function store(): Response
    {
        $input = $this->request()->request->all();

        try {
            $this->validateCsrf($input);

            $shiftRequest = new ShiftRequest(
                $input,
                $this->settings->defaultShiftCompensation()
            );

            $data = $shiftRequest->all();
            $this->access->requireManage((int) $data['event_id']);

            $id = $this->service->create(
                $data
            );

            $this->success(
                'De shift werd succesvol aangemaakt.'
            );

            return $this->redirect(
                '/shifts/' . $id
            );
        } catch (Throwable $throwable) {
            $this->flashValidationFailure(
                $input,
                $throwable,
                'De shift kon niet worden aangemaakt.'
            );

            return $this->redirect(
                '/shifts/create'
            );
        }
    }

    public function edit(): Response
    {
        $shiftId = $this->routeId();
        $shift = $this->service->find($shiftId);

        if ($shift === null) {
            return $this->notFound(
                'Shift niet gevonden.'
            );
        }

        if (!$this->access->canManage($shift->eventId)) {
            return $this->forbidden();
        }

        return $this->view(
            'shifts.edit',
            [
                'title' => 'Shift wijzigen',
                'shift' => $shift,
                'events' => Auth::isAdmin()
                    ? $this->eventRepository->allForAdministration()
                    : $this->managedEvents(),
                'shiftTypes' => $this->service
                    ->allTypes(),
                'defaultShiftCompensation' => $this->settings
                    ->defaultShiftCompensation(),
                'groupSupplement' => $this->settings
                    ->groupSupplement(),
            ]
        );
    }

    public function update(): Response
    {
        $shiftId = $this->routeId();
        $input = $this->request()->request->all();
        $shift = $this->service->find($shiftId);

        if ($shift === null) {
            return $this->notFound(
                'Shift niet gevonden.'
            );
        }

        if (!$this->access->canManage($shift->eventId)) {
            return $this->forbidden();
        }

        try {
            $this->validateCsrf($input);

            $shiftRequest = new ShiftRequest(
                $input,
                $shift->vergoedingBedrag
            );

            $data = $shiftRequest->all();
            $this->access->requireManage((int) $data['event_id']);

            $this->service->update(
                $shiftId,
                $data
            );

            $this->success(
                'De shift werd succesvol gewijzigd.'
            );

            return $this->redirect(
                '/shifts/' . $shiftId
            );
        } catch (Throwable $throwable) {
            $this->flashValidationFailure(
                $input,
                $throwable,
                'De shift kon niet worden gewijzigd.'
            );

            return $this->redirect(
                '/shifts/' . $shiftId . '/edit'
            );
        }
    }

    public function cancelShift(): Response
    {
        $shiftId = $this->routeId();
        $input = $this->request()->request->all();
        $shift = $this->service->find($shiftId);

        if ($shift === null) {
            return $this->notFound('Shift niet gevonden.');
        }

        if (!$this->access->canManage($shift->eventId)) {
            return $this->forbidden();
        }

        try {
            $this->validateCsrf($input);

            $request = new ShiftRegistrationRequest(
                $input
            );

            $data = $request->all();

            $cancelledRegistrations = $this->service
                ->cancelShift(
                    $shiftId,
                    $data['annulatie_reden']
                );

            $this->success(
                sprintf(
                    'De shift werd geannuleerd. %d actieve inschrijving(en) werden eveneens geannuleerd.',
                    $cancelledRegistrations
                )
            );
        } catch (Throwable $throwable) {
            $this->error(
                $throwable->getMessage()
            );
        }

        return $this->redirect(
            '/shifts/' . $shiftId
        );
    }

    public function destroy(): Response
    {
        $shiftId = $this->routeId();
        $input = $this->request()->request->all();
        $shift = $this->service->find($shiftId);

        if ($shift === null) {
            return $this->notFound('Shift niet gevonden.');
        }

        if (!$this->access->canManage($shift->eventId)) {
            return $this->forbidden();
        }

        try {
            $this->validateCsrf($input);

            $this->service->delete($shiftId);

            $this->success(
                'De shift werd succesvol verwijderd.'
            );

            return $this->redirect('/shifts');
        } catch (Throwable $throwable) {
            $this->error(
                $throwable->getMessage()
            );

            return $this->redirect(
                '/shifts/' . $shiftId
            );
        }
    }

    public function assign(): Response
    {
        $shiftId = $this->routeId();
        $input = $this->request()->request->all();
        $shift = $this->service->find($shiftId);

        if ($shift === null) {
            return $this->notFound('Shift niet gevonden.');
        }

        if (!$this->access->canManage($shift->eventId)) {
            return $this->forbidden();
        }

        $redirect = '/shifts/' . $shiftId;
        try {
            $this->validateCsrf($input);

            $request = new ShiftRegistrationRequest(
                $input
            );

            $data = $request->all();

            $sourceId = (int) ($input['companion_from'] ?? 0);
            $chain = trim((string) ($input['companion_chain'] ?? ''));
            if ($sourceId > 0) {
                $chainIds = array_values(array_filter(array_map(
                    'intval',
                    explode(',', $chain)
                ), static fn(int $id): bool => $id > 0));
                if (count($chainIds) > 50
                    || end($chainIds) !== $sourceId
                    || !in_array(
                        $data['lid_id'],
                        $this->companions->selectedIds($shift->eventId, $sourceId),
                        true
                    )) {
                    throw new DomainException('Deze voorgestelde deelnemer is niet geldig.');
                }
                $assignedSource = false;
                foreach ($this->service->registrationsForShift($shiftId) as $assignment) {
                    if ($assignment->lidId === $sourceId && $assignment->isActief()) {
                        $assignedSource = true;
                        break;
                    }
                }
                if (!$assignedSource) {
                    throw new DomainException('De oorspronkelijke deelnemer is niet aan deze shift toegewezen.');
                }
            }

            $this->service->assignByAdmin(
                shiftId: $shiftId,
                memberId: $data['lid_id'],
                status: $data['status']
            );

            $this->success(
                'De vrijwilliger werd aan de shift toegewezen.'
            );
            if ($data['status'] === 'bevestigd') {
                $nextChain = $sourceId > 0
                    ? $chain . ',' . $data['lid_id']
                    : (string) $data['lid_id'];
                $redirect .= '?companion_chain=' . rawurlencode($nextChain);
            }
        } catch (Throwable $throwable) {
            $this->error(
                $throwable->getMessage()
            );
        }

        return $this->redirect($redirect);
    }

    public function approve(): Response
    {
        return $this->handleDecision(
            static function (
                ShiftService $service,
                int $registrationId
            ): void {
                $service->approve(
                    $registrationId
                );
            },
            'De inschrijving werd goedgekeurd.',
            true
        );
    }

    public function reserve(): Response
    {
        return $this->handleDecision(
            static function (
                ShiftService $service,
                int $registrationId
            ): void {
                $service->reserve(
                    $registrationId
                );
            },
            'De inschrijving werd op de reservelijst geplaatst.'
        );
    }

    public function reject(): Response
    {
        return $this->handleDecision(
            static function (
                ShiftService $service,
                int $registrationId
            ): void {
                $service->reject(
                    $registrationId
                );
            },
            'De inschrijving werd geweigerd.'
        );
    }

    public function cancelRegistration(): Response
    {
        $registrationId = $this->routeId(
            'registrationId'
        );

        $registration = $this->service
            ->findRegistration(
                $registrationId
            );

        if ($registration === null) {
            return $this->notFound(
                'Shiftinschrijving niet gevonden.'
            );
        }

        $shift = $this->service->find($registration->shiftId);

        if ($shift === null || !$this->access->canManage($shift->eventId)) {
            return $this->forbidden();
        }

        $input = $this->request()->request->all();

        try {
            $this->validateCsrf($input);

            $request = new ShiftRegistrationRequest(
                $input
            );

            $data = $request->all();

            $this->service->cancelByAdmin(
                $registrationId,
                $data['annulatie_reden']
            );

            $this->success(
                'De shiftinschrijving werd geannuleerd.'
            );
        } catch (Throwable $throwable) {
            $this->error(
                $throwable->getMessage()
            );
        }

        return $this->redirect(
            '/shifts/' . $registration->shiftId
        );
    }

    public function presence(): Response
    {
        $expectsJson = $this->request()->isAjax()
            || $this->request()->acceptsJson();

        $registrationId = $this->routeId(
            'registrationId'
        );

        $registration = $this->service
            ->findRegistration(
                $registrationId
            );

        if ($registration === null) {
            if ($expectsJson) {
                return new JsonResponse(
                    [
                        'success' => false,
                        'present' => false,
                        'message' => 'Shiftinschrijving niet gevonden.',
                    ],
                    404
                );
            }

            return $this->notFound(
                'Shiftinschrijving niet gevonden.'
            );
        }

        $shift = $this->service->find($registration->shiftId);

        if ($shift === null || !$this->access->canManage($shift->eventId)) {
            return $this->forbidden();
        }

        $input = $this->request()->request->all();

        try {
            $this->validateCsrf($input);
        } catch (Throwable $throwable) {
            if ($expectsJson) {
                return new JsonResponse(
                    [
                        'success' => false,
                        'present' => $registration->aanwezig,
                        'message' => $throwable->getMessage(),
                    ],
                    419
                );
            }

            $this->error(
                $throwable->getMessage()
            );

            return $this->redirect(
                '/shifts/' . $registration->shiftId
            );
        }

        try {
            $request = new ShiftRegistrationRequest(
                $input
            );

            $data = $request->all();

            $this->service->setPresence(
                $registrationId,
                $data['aanwezig']
            );

            $message = $data['aanwezig']
                ? 'De vrijwilliger werd als aanwezig gemarkeerd.'
                : 'De aanwezigheidsmarkering werd verwijderd.';

            if ($expectsJson) {
                return new JsonResponse(
                    [
                        'success' => true,
                        'present' => $data['aanwezig'],
                        'message' => $message,
                    ]
                );
            }

            $this->success($message);
        } catch (Throwable $throwable) {
            if ($expectsJson) {
                $isExpectedFailure = $throwable instanceof DomainException
                    || $throwable instanceof InvalidArgumentException;

                return new JsonResponse(
                    [
                        'success' => false,
                        'present' => $registration->aanwezig,
                        'message' => $isExpectedFailure
                            ? $throwable->getMessage()
                            : 'De aanwezigheidsstatus kon niet worden bijgewerkt.',
                    ],
                    $throwable instanceof InvalidArgumentException
                        ? 404
                        : ($isExpectedFailure ? 422 : 500)
                );
            }

            $this->error(
                $throwable->getMessage()
            );
        }

        return $this->redirect(
            '/shifts/' . $registration->shiftId
        );
    }

    /**
     * @param callable(ShiftService, int): void $decision
     */
    private function handleDecision(
        callable $decision,
        string $successMessage,
        bool $offerCompanions = false
    ): Response {
        $registrationId = $this->routeId(
            'registrationId'
        );

        $registration = $this->service
            ->findRegistration(
                $registrationId
            );

        if ($registration === null) {
            return $this->notFound(
                'Shiftinschrijving niet gevonden.'
            );
        }

        $shift = $this->service->find($registration->shiftId);

        if ($shift === null || !$this->access->canManage($shift->eventId)) {
            return $this->forbidden();
        }

        $input = $this->request()->request->all();
        $redirect = '/shifts/' . $registration->shiftId;

        try {
            $this->validateCsrf($input);

            $decision(
                $this->service,
                $registrationId
            );

            $this->success($successMessage);
            if ($offerCompanions && !$registration->isBevestigd()) {
                $redirect .= '?companion_chain=' . $registration->lidId;
            }
        } catch (Throwable $throwable) {
            $this->error(
                $throwable->getMessage()
            );
        }

        return $this->redirect($redirect);
    }

    /**
     * @param array<string, mixed> $input
     */
    private function validateCsrf(array $input): void
    {
        $token = $input['_token'] ?? null;

        if (
            !is_string($token)
            || !$this->csrf->validate($token)
        ) {
            throw new RuntimeException(
                'De beveiligingstoken is ongeldig of verlopen. Probeer opnieuw.'
            );
        }
    }

    /**
     * @param array<string, mixed> $input
     */
    private function flashValidationFailure(
        array $input,
        Throwable $throwable,
        string $flashMessage
    ): void {
        unset($input['_token']);

        Session::flash(
            '_old_input',
            $input
        );

        Session::flash(
            '_errors',
            [
                'form' => [
                    $throwable->getMessage(),
                ],
            ]
        );

        $this->error($flashMessage);
    }

    private function routeId(
        string $key = 'id'
    ): int {
        return (int) $this->request()->route(
            $key,
            0
        );
    }

    private function notFound(
        string $message
    ): Response {
        return $this->view(
            'core::errors.404',
            [
                'message' => $message,
            ],
            404
        );
    }

    /** @return array<int, \App\Models\Event> */
    private function managedEvents(): array
    {
        $memberId = Auth::memberId();

        return $memberId !== null
            ? $this->eventRepository->managedByMember($memberId)
            : [];
    }

    private function forbidden(): Response
    {
        return $this->view(
            'core::errors.403',
            ['message' => 'Je hebt geen beheerrechten voor dit evenement.'],
            403
        );
    }
}
