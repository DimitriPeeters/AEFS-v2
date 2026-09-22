<?php

declare(strict_types=1);

namespace App\Controllers;

use AEFS\Core\Auth;
use AEFS\Core\Http\Request;
use AEFS\Core\Http\Response;
use AEFS\Core\Session;
use AEFS\Core\View\Helper\CsrfHelper;
use AEFS\Core\View\ViewFactory;
use App\Http\Requests\EventCancellationRequest;
use App\Http\Requests\EventRegistrationRequest;
use App\Http\Requests\EventRequest;
use App\Services\EventAccessService;
use App\Services\EventRegistrationProfileService;
use App\Services\EventService;
use App\Services\SettingsService;
use RuntimeException;
use Throwable;

final class EventController extends BaseController
{
    public function __construct(
        ViewFactory $views,
        Request $request,
        private readonly EventService $service,
        private readonly EventAccessService $access,
        private readonly EventRegistrationProfileService $profiles,
        private readonly SettingsService $settings,
        private readonly CsrfHelper $csrf
    ) {
        parent::__construct(
            $views,
            $request
        );
    }

    public function index(): Response
    {
        $zoekterm = trim(
            (string) $this->request()->query->get(
                'zoek',
                ''
            )
        );

        $isAdmin = Auth::isAdmin();

        if ($isAdmin) {
            $events = $zoekterm === ''
                ? $this->service->allForAdministration()
                : $this->service->searchForAdministration($zoekterm);
        } else {
            $events = $this->service->visibleOrManagedForCurrentMember(
                $zoekterm
            );
        }

        $manageableEventIds = [];

        foreach ($events as $listedEvent) {
            if ($this->access->canManage($listedEvent->eventId)) {
                $manageableEventIds[] = $listedEvent->eventId;
            }
        }

        return $this->view(
            'events.index',
            [
                'title' => 'Evenementen',
                'events' => $events,
                'zoekterm' => $zoekterm,
                'isAdmin' => $isAdmin,
                'manageableEventIds' => $manageableEventIds,
            ]
        );
    }

    public function show(): Response
    {
        $id = $this->routeId();
        $isAdmin = Auth::isAdmin();
        $canManageEvent = $this->access->canManage($id);

        $event = $canManageEvent
            ? $this->service->find($id)
            : $this->service->findVisibleToMembers($id);

        if ($event === null) {
            return $this->notFound();
        }

        $memberId = Auth::memberId();
        $canManageOwnRegistration = $memberId !== null
            && $memberId > 0
            && $this->service->findVisibleToMembers($id) !== null;
        $missingProfileFields = $canManageOwnRegistration
            ? $this->profiles->missingFields($memberId)
            : [];

        return $this->view(
            'events.show',
            [
                'title' => $event->titel,
                'event' => $event,
                'isAdmin' => $isAdmin,
                'canManageEvent' => $canManageEvent,
                'canManageOwnRegistration' => $canManageOwnRegistration,
                'registration' => $canManageOwnRegistration
                    ? $this->service->registrationForMember(
                        $id,
                        $memberId
                    )
                    : null,
                'registrations' => $canManageEvent
                    ? $this->service->registrationsForEvent($id)
                    : [],
                'shifts' => $canManageEvent
                    ? $this->service->shiftsForEvent($id)
                    : [],
                'missingProfileFields' => $missingProfileFields,
                'profileValues' => $canManageOwnRegistration
                    ? $this->profiles->formValues($memberId)
                    : [],
                'openProfileCompletion' => Session::getFlash(
                    '_open_profile_completion',
                    false
                ) === true,
            ]
        );
    }

    public function create(): Response
    {
        return $this->view(
            'events.create',
            [
                'title' => 'Nieuw evenement',
                'shiftTypes' => $this->service->activeShiftTypes(),
                'defaultShiftCompensation' => $this->settings
                    ->defaultShiftCompensation(),
                'defaultGroupSupplement' => $this->settings
                    ->groupSupplement(),
                'defaultEventUsesGroups' => $this->settings
                    ->defaultEventUsesGroups(),
                'publicationGroups' => $this->service
                    ->publicationGroupOptions(),
                'canManageAssignments' => true,
                'managerOptions' => $this->access->managerOptions(),
                'visibilityGroupOptions' => $this->access->groupOptions(),
                'selectedManagerIds' => [],
                'selectedVisibilityGroupIds' => [],
            ]
        );
    }

    public function store(): Response
    {
        $input = $this->request()->request->all();

        try {
            $this->validateCsrf($input);

            $eventRequest = new EventRequest(
                $input,
                $this->settings->defaultShiftCompensation(),
                $this->settings->defaultEventUsesGroups(),
                $this->settings->groupSupplement()
            );
            $id = $this->service->create(
                $eventRequest->all(),
                $eventRequest->shifts(),
                $eventRequest->publicationAudience(),
                $eventRequest->managerIds(),
                $eventRequest->visibilityGroupIds()
            );

            $this->success(
                'Het evenement en de opgegeven shifts werden succesvol aangemaakt.'
            );

            return $this->redirect('/events/' . $id);
        } catch (Throwable $throwable) {
            $this->flashValidationFailure(
                $input,
                $throwable,
                'Het evenement kon niet worden aangemaakt.'
            );

            return $this->redirect('/events/create');
        }
    }

    public function edit(): Response
    {
        $id = $this->routeId();

        if (!$this->access->canManage($id)) {
            return $this->forbidden();
        }

        $event = $this->service->find($id);

        if ($event === null) {
            return $this->notFound();
        }

        return $this->view(
            'events.edit',
            [
                'title' => 'Evenement wijzigen',
                'event' => $event,
                'shiftTypes' => $this->service->activeShiftTypes(),
                'shifts' => $this->service->shiftsForEvent($id),
                'defaultShiftCompensation' => $this->settings
                    ->defaultShiftCompensation(),
                'defaultGroupSupplement' => $this->settings
                    ->groupSupplement(),
                'defaultEventUsesGroups' => false,
                'publicationGroups' => $this->service
                    ->publicationGroupOptions(),
                'canManageAssignments' => Auth::isAdmin(),
                'managerOptions' => Auth::isAdmin()
                    ? $this->access->managerOptions()
                    : [],
                'visibilityGroupOptions' => Auth::isAdmin()
                    ? $this->access->groupOptions()
                    : [],
                'selectedManagerIds' => $this->access->managerIds($id),
                'selectedVisibilityGroupIds' => $this->access->groupIds($id),
            ]
        );
    }

    public function update(): Response
    {
        $id = $this->routeId();
        $input = $this->request()->request->all();
        $event = $this->service->find($id);

        if ($event === null) {
            return $this->notFound();
        }

        if (!$this->access->canManage($id)) {
            return $this->forbidden();
        }

        try {
            $this->validateCsrf($input);

            $eventRequest = new EventRequest(
                $input,
                $this->settings->defaultShiftCompensation(),
                $event->werktMetGroepen,
                $event->groepstoeslagBedrag
            );
            $data = $eventRequest->all();

            $this->service->update(
                $id,
                $data,
                $eventRequest->shifts(),
                $eventRequest->publicationAudience(),
                Auth::isAdmin() ? $eventRequest->managerIds() : null,
                Auth::isAdmin()
                    ? $eventRequest->visibilityGroupIds()
                    : null
            );

            $this->success(
                ($data['status'] ?? null) === 'geannuleerd'
                    ? 'Het evenement werd geannuleerd. Betrokken leden worden per mail verwittigd; hun actieve inschrijvingen en shifts worden na succesvolle aflevering automatisch geannuleerd.'
                    : 'Het evenement en de opgegeven shifts werden succesvol gewijzigd.'
            );

            return $this->redirect('/events/' . $id);
        } catch (Throwable $throwable) {
            $this->flashValidationFailure(
                $input,
                $throwable,
                'Het evenement kon niet worden gewijzigd.'
            );

            return $this->redirect('/events/' . $id . '/edit');
        }
    }

    public function destroy(): Response
    {
        $id = $this->routeId();
        $input = $this->request()->request->all();

        try {
            $this->validateCsrf($input);
            $this->service->delete($id);

            $this->success(
                'Het evenement werd succesvol verwijderd.'
            );
        } catch (Throwable $throwable) {
            $this->error($throwable->getMessage());
        }

        return $this->redirect('/events');
    }

    public function register(): Response
    {
        $eventId = $this->routeId();
        $input = $this->request()->request->all();

        try {
            $this->validateCsrf($input);

            $request = new EventRegistrationRequest($input);
            $data = $request->all();
            $memberId = $this->requireMemberId();

            if ($this->profiles->missingFields($memberId) !== []) {
                Session::flash('_open_profile_completion', true);

                throw new RuntimeException(
                    'Vul eerst de ontbrekende persoonsgegevens en adresgegevens aan.'
                );
            }

            $this->service->registerMember(
                $eventId,
                $memberId,
                $data['dagen']
            );

            $this->success(
                'Je beschikbaarheid werd geregistreerd en wacht op beoordeling.'
            );
        } catch (Throwable $throwable) {
            $this->flashRegistrationFailure($input, $throwable);
        }

        return $this->redirect('/events/' . $eventId);
    }

    public function completeProfileAndRegister(): Response
    {
        $eventId = $this->routeId();
        $input = $this->request()->request->all();

        try {
            $this->validateCsrf($input);
            $memberId = $this->requireMemberId();
            $request = new EventRegistrationRequest($input);
            $data = $request->all();

            $this->profiles->complete($memberId, $input);
            $this->service->registerMember(
                $eventId,
                $memberId,
                $data['dagen']
            );

            $this->success(
                'Je profiel werd aangevuld en je beschikbaarheid werd geregistreerd.'
            );
        } catch (Throwable $throwable) {
            unset($input['_token']);
            Session::flash('_old_input', $input);
            Session::flash('_open_profile_completion', true);
            $this->error($throwable->getMessage());
        }

        return $this->redirect('/events/' . $eventId);
    }

    public function cancelRegistration(): Response
    {
        $eventId = $this->routeId();
        $input = $this->request()->request->all();

        try {
            $this->validateCsrf($input);

            $request = new EventCancellationRequest($input);
            $data = $request->all();
            $requiresVerification = $this->service
                ->requestRegistrationCancellation(
                    $eventId,
                    $this->requireMemberId(),
                    $data['reden']
                );

            $this->success(
                $requiresVerification
                    ? 'Je annulatieaanvraag werd geregistreerd en wacht op verificatie door een administrator.'
                    : 'Je evenementinschrijving werd geannuleerd.'
            );
        } catch (Throwable $throwable) {
            $this->error($throwable->getMessage());
        }

        return $this->redirect('/events/' . $eventId);
    }

    public function confirmRegistrationCancellation(): Response
    {
        $registrationId = $this->routeId('registrationId');
        $registration = $this->service->findRegistration($registrationId);

        if ($registration === null) {
            return $this->notFound(
                'Evenementinschrijving niet gevonden.'
            );
        }

        if (!$this->access->canManage($registration->eventId)) {
            return $this->forbidden();
        }

        $input = $this->request()->request->all();

        try {
            $this->validateCsrf($input);
            $cancelledAssignments = $this->service
                ->confirmRegistrationCancellation($registrationId);

            $message = 'De annulatieaanvraag werd bevestigd.';

            if ($cancelledAssignments > 0) {
                $message .= sprintf(
                    ' %d actieve shifttoewijzing(en) werden eveneens geannuleerd.',
                    $cancelledAssignments
                );
            }

            $this->success($message);
        } catch (Throwable $throwable) {
            $this->error($throwable->getMessage());
        }

        return $this->redirect('/events/' . $registration->eventId);
    }

    public function approveRegistration(): Response
    {
        return $this->handleRegistrationDecision(
            static function (
                EventService $service,
                int $registrationId
            ): void {
                $service->approveRegistration($registrationId);
            },
            'De evenementinschrijving werd bevestigd.'
        );
    }

    public function reserveRegistration(): Response
    {
        return $this->handleRegistrationDecision(
            static function (
                EventService $service,
                int $registrationId
            ): void {
                $service->reserveRegistration($registrationId);
            },
            'De evenementinschrijving werd op reserve geplaatst.'
        );
    }

    public function rejectRegistration(): Response
    {
        return $this->handleRegistrationDecision(
            static function (
                EventService $service,
                int $registrationId
            ): void {
                $service->rejectRegistration($registrationId);
            },
            'De evenementinschrijving werd geweigerd.'
        );
    }

    /**
     * @param callable(EventService, int): void $decision
     */
    private function handleRegistrationDecision(
        callable $decision,
        string $successMessage
    ): Response {
        $registrationId = $this->routeId('registrationId');
        $registration = $this->service->findRegistration($registrationId);

        if ($registration === null) {
            return $this->notFound(
                'Evenementinschrijving niet gevonden.'
            );
        }

        if (!$this->access->canManage($registration->eventId)) {
            return $this->forbidden();
        }

        $input = $this->request()->request->all();

        try {
            $this->validateCsrf($input);
            $decision($this->service, $registrationId);
            $this->success($successMessage);
        } catch (Throwable $throwable) {
            $this->error($throwable->getMessage());
        }

        return $this->redirect('/events/' . $registration->eventId);
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

        Session::flash('_old_input', $input);
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

    /**
     * @param array<string, mixed> $input
     */
    private function flashRegistrationFailure(
        array $input,
        Throwable $throwable
    ): void {
        unset($input['_token']);
        Session::flash('_old_input', $input);
        $this->error($throwable->getMessage());
    }

    private function requireMemberId(): int
    {
        $memberId = Auth::memberId();

        if ($memberId === null || $memberId <= 0) {
            throw new RuntimeException(
                'Aan dit gebruikersaccount is geen geldig lid gekoppeld.'
            );
        }

        return $memberId;
    }

    private function routeId(string $key = 'id'): int
    {
        return (int) $this->request()->route($key, 0);
    }

    private function notFound(
        string $message = 'Evenement niet gevonden.'
    ): Response {
        return $this->view(
            'core::errors.404',
            [
                'message' => $message,
            ],
            404
        );
    }

    public function sendConfirmations(): Response
    {
        $eventId = $this->routeId();

        if (!$this->access->canManage($eventId)) {
            return $this->forbidden();
        }

        try {
            $this->validateCsrf($this->request()->request->all());
            $count = $this->service->sendConfirmationMails($eventId);
            $this->success(
                $count . ' bevestigingsmail(s) werden in de verzendwachtrij geplaatst.'
            );
        } catch (Throwable $throwable) {
            $this->error($throwable->getMessage());
        }

        return $this->redirect('/events/' . $eventId);
    }

    private function forbidden(): Response
    {
        return $this->view(
            'core::errors.403',
            [
                'message' => 'Je hebt geen beheerrechten voor dit evenement.',
            ],
            403
        );
    }
}
