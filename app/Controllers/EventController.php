<?php

declare(strict_types=1);

namespace AEFS\Controllers;

use AEFS\Core\Request;
use AEFS\Core\Response;
use AEFS\Core\View;
use AEFS\Services\EventService;
use Throwable;

final class EventController
{
    public function __construct(
        private EventService $service
    ) {
    }

    public function index(Request $request): void
    {
        $zoekterm = trim((string) $request->query('q', ''));

        $events = $zoekterm === ''
            ? $this->service->all()
            : $this->service->search($zoekterm);

        Response::html(
            View::render('events/index', [
                'title'     => 'Evenementen',
                'events'    => $events,
                'zoekterm'  => $zoekterm,
            ])
        );
    }

    public function show(Request $request): void
    {
        $id = (int) $request->route('id');

        $event = $this->service->find($id);

        if ($event === null) {
            Response::notFound();
            return;
        }

        Response::html(
            View::render('events/show', [
                'title' => $event->titel,
                'event' => $event,
            ])
        );
    }

    public function create(): void
    {
        Response::html(
            View::render('events/create', [
                'title' => 'Nieuw evenement',
            ])
        );
    }

    public function store(Request $request): void
    {
        try {

            $id = $this->service->create(
                $request->all()
            );

            $_SESSION['success'] = 'Evenement succesvol aangemaakt.';

            Response::redirect('/events/' . $id);

        } catch (Throwable $e) {

            Response::html(
                View::render('events/create', [
                    'title'  => 'Nieuw evenement',
                    'errors' => [$e->getMessage()],
                    'old'    => $request->all(),
                ])
            );

        }
    }

    public function edit(Request $request): void
    {
        $id = (int) $request->route('id');

        $event = $this->service->find($id);

        if ($event === null) {
            Response::notFound();
            return;
        }

        Response::html(
            View::render('events/edit', [
                'title' => 'Evenement wijzigen',
                'event' => $event,
            ])
        );
    }

    public function update(Request $request): void
    {
        $id = (int) $request->route('id');

        try {

            $this->service->update(
                $id,
                $request->all()
            );

            $_SESSION['success'] = 'Evenement succesvol gewijzigd.';

            Response::redirect('/events/' . $id);

        } catch (Throwable $e) {

            Response::html(
                View::render('events/edit', [
                    'title'  => 'Evenement wijzigen',
                    'event'  => $this->service->find($id),
                    'errors' => [$e->getMessage()],
                ])
            );

        }
    }

    public function destroy(Request $request): void
    {
        $id = (int) $request->route('id');

        try {

            $this->service->delete($id);

            $_SESSION['success'] = 'Evenement verwijderd.';

        } catch (Throwable $e) {

            $_SESSION['error'] = $e->getMessage();

        }

        Response::redirect('/events');
    }

    public function activate(Request $request): void
    {
        $this->service->activate(
            (int) $request->route('id')
        );

        $_SESSION['success'] = 'Evenement geactiveerd.';

        Response::redirect('/events');
    }

    public function deactivate(Request $request): void
    {
        $this->service->deactivate(
            (int) $request->route('id')
        );

        $_SESSION['success'] = 'Evenement gedeactiveerd.';

        Response::redirect('/events');
    }
}