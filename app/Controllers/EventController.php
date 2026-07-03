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
                'events'   => $events,
                'zoekterm' => $zoekterm,
            ])
        );
    }

    public function show(Request $request): void
    {
        $id = (int) $request->route('id');

        $event = $this->service->find($id);

        if ($event === null) {
            Response::notFound();
        }

        Response::html(
            View::render('events/show', [
                'event' => $event,
            ])
        );
    }

    public function create(): void
    {
        Response::html(
            View::render('events/create')
        );
    }

    public function store(Request $request): void
    {
        try {

            $this->service->create(
                $request->all()
            );

            $_SESSION['success'] = 'Evenement succesvol aangemaakt.';

            Response::redirect('/events');

        } catch (Throwable $e) {

            $_SESSION['error'] = $e->getMessage();

            Response::back();

        }
    }

    public function edit(Request $request): void
    {
        $id = (int) $request->route('id');

        $event = $this->service->find($id);

        if ($event === null) {
            Response::notFound();
        }

        Response::html(
            View::render('events/edit', [
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

            Response::redirect('/events');

        } catch (Throwable $e) {

            $_SESSION['error'] = $e->getMessage();

            Response::back();

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
