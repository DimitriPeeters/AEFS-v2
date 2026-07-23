<?php

declare(strict_types=1);

namespace App\Controllers;

use AEFS\Core\Auth;
use AEFS\Core\Http\Request;
use AEFS\Core\Http\Response;
use AEFS\Core\Session;
use AEFS\Core\View\Helper\CsrfHelper;
use AEFS\Core\View\ViewFactory;
use App\Http\Requests\EventRequest;
use App\Services\EventService;
use RuntimeException;
use Throwable;

final class EventController extends BaseController
{
    public function __construct(
        ViewFactory $views,
        Request $request,
        private readonly EventService $service,
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
            $events = $zoekterm === ''
                ? $this->service->visibleToMembers()
                : $this->service->searchVisibleToMembers($zoekterm);
        }

        return $this->view(
            'events.index',
            [
                'title' => 'Evenementen',
                'events' => $events,
                'zoekterm' => $zoekterm,
                'isAdmin' => $isAdmin,
            ]
        );
    }

    public function show(): Response
    {
        $id = $this->routeId();
        $isAdmin = Auth::isAdmin();

        $event = $isAdmin
            ? $this->service->find($id)
            : $this->service->findVisibleToMembers($id);

        if ($event === null) {
            return $this->notFound();
        }

        return $this->view(
            'events.show',
            [
                'title' => $event->titel,
                'event' => $event,
                'isAdmin' => $isAdmin,
            ]
        );
    }

    public function create(): Response
    {
        return $this->view(
            'events.create',
            [
                'title' => 'Nieuw evenement',
            ]
        );
    }

    public function store(): Response
    {
        $input = $this->request()->request->all();

        try {
            $this->validateCsrf($input);

            $eventRequest = new EventRequest($input);
            $id = $this->service->create(
                $eventRequest->all()
            );

            $this->success(
                'Het evenement werd succesvol aangemaakt.'
            );

            return $this->redirect(
                '/events/' . $id
            );
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
        $event = $this->service->find($id);

        if ($event === null) {
            return $this->notFound();
        }

        return $this->view(
            'events.edit',
            [
                'title' => 'Evenement wijzigen',
                'event' => $event,
            ]
        );
    }

    public function update(): Response
    {
        $id = $this->routeId();
        $input = $this->request()->request->all();

        try {
            $this->validateCsrf($input);

            $eventRequest = new EventRequest($input);

            $this->service->update(
                $id,
                $eventRequest->all()
            );

            $this->success(
                'Het evenement werd succesvol gewijzigd.'
            );

            return $this->redirect(
                '/events/' . $id
            );
        } catch (Throwable $throwable) {
            $this->flashValidationFailure(
                $input,
                $throwable,
                'Het evenement kon niet worden gewijzigd.'
            );

            return $this->redirect(
                '/events/' . $id . '/edit'
            );
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
            $this->error(
                $throwable->getMessage()
            );
        }

        return $this->redirect('/events');
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

    private function routeId(): int
    {
        return (int) $this->request()->route(
            'id',
            0
        );
    }

    private function notFound(): Response
    {
        return $this->view(
            'core::errors.404',
            [
                'message' => 'Evenement niet gevonden.',
            ],
            404
        );
    }
}
