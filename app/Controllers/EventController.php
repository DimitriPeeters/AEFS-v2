<?php

declare(strict_types=1);

namespace App\Controllers;

use AEFS\Core\Http\Request;
use AEFS\Core\Http\Response;
use AEFS\Core\Session;
use AEFS\Core\View\ViewFactory;
use App\Services\EventService;
use Throwable;

final class EventController extends BaseController
{
    public function __construct(
        ViewFactory $views,
        Request $request,
        private readonly EventService $service
    ) {
        parent::__construct(
            $views,
            $request
        );
    }

    public function index(): Response
    {
        $zoekterm = trim(
            (string) $this->request()->query(
                'q',
                ''
            )
        );

        $events = $zoekterm === ''
            ? $this->service->all()
            : $this->service->search($zoekterm);

        return $this->view(
            'events.index',
            [
                'title' => 'Evenementen',
                'events' => $events,
                'zoekterm' => $zoekterm,
            ]
        );
    }

    public function show(): Response
    {
        $id = $this->routeId();

        $event = $this->service->find($id);

        if ($event === null) {
            return $this->view(
                'core::errors.404',
                [
                    'message' => 'Evenement niet gevonden.',
                ],
                404
            );
        }

        return $this->view(
            'events.show',
            [
                'title' => $event->titel,
                'event' => $event,
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
        $input = $this->request()->all();

        Session::flash(
            '_old_input',
            $input
        );

        try {
            $id = $this->service->create($input);

            $this->success(
                'Evenement succesvol aangemaakt.'
            );

            return $this->redirect(
                '/events/' . $id
            );
        } catch (Throwable $throwable) {
            Session::flash(
                '_errors',
                [
                    'form' => [
                        $throwable->getMessage(),
                    ],
                ]
            );

            $this->error(
                'Het evenement kon niet worden aangemaakt.'
            );

            return $this->redirect(
                '/events/create'
            );
        }
    }

    public function edit(): Response
    {
        $id = $this->routeId();

        $event = $this->service->find($id);

        if ($event === null) {
            return $this->view(
                'core::errors.404',
                [
                    'message' => 'Evenement niet gevonden.',
                ],
                404
            );
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
        $input = $this->request()->all();

        Session::flash(
            '_old_input',
            $input
        );

        try {
            $this->service->update(
                $id,
                $input
            );

            $this->success(
                'Evenement succesvol gewijzigd.'
            );

            return $this->redirect(
                '/events/' . $id
            );
        } catch (Throwable $throwable) {
            Session::flash(
                '_errors',
                [
                    'form' => [
                        $throwable->getMessage(),
                    ],
                ]
            );

            $this->error(
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

        $event = $this->service->find($id);

        if ($event === null) {
            return $this->view(
                'core::errors.404',
                [
                    'message' => 'Evenement niet gevonden.',
                ],
                404
            );
        }

        try {
            $this->service->delete($id);

            $this->success(
                'Evenement succesvol verwijderd.'
            );
        } catch (Throwable $throwable) {
            $this->error(
                $throwable->getMessage()
            );
        }

        return $this->redirect('/events');
    }

    public function activate(): Response
    {
        $id = $this->routeId();

        try {
            $this->service->activate($id);

            $this->success(
                'Evenement geactiveerd.'
            );
        } catch (Throwable $throwable) {
            $this->error(
                $throwable->getMessage()
            );
        }

        return $this->redirect('/events');
    }

    public function deactivate(): Response
    {
        $id = $this->routeId();

        try {
            $this->service->deactivate($id);

            $this->success(
                'Evenement gedeactiveerd.'
            );
        } catch (Throwable $throwable) {
            $this->error(
                $throwable->getMessage()
            );
        }

        return $this->redirect('/events');
    }

    private function routeId(): int
    {
        return (int) $this->request()->route(
            'id',
            0
        );
    }
}