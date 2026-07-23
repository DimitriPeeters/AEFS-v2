<?php

declare(strict_types=1);

namespace App\Controllers;

use AEFS\Core\Http\Request;
use AEFS\Core\Http\Response;
use AEFS\Core\Session;
use AEFS\Core\View\ViewFactory;
use AEFS\Http\Requests\UserRequest;
use App\Services\AuditLogService;
use App\Services\MemberService;
use App\Services\UserService;
use Throwable;

final class UserController extends BaseController
{
    public function __construct(
        ViewFactory $views,
        Request $request,
        private readonly UserService $userService,
        private readonly MemberService $memberService,
        private readonly AuditLogService $auditLog
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
                'zoek',
                ''
            )
        );

        $gebruikers = $zoekterm === ''
            ? $this->userService->all()
            : $this->userService->search($zoekterm);

        return $this->view(
            'users.index',
            [
                'title' => 'Gebruikers',
                'titel' => 'Gebruikers',
                'zoekterm' => $zoekterm,
                'gebruikers' => $gebruikers,
            ]
        );
    }

    public function show(): Response
    {
        $id = $this->routeId();

        $gebruiker = $this->userService->find($id);

        if ($gebruiker === null) {
            return $this->view(
                'core::errors.404',
                [
                    'message' => 'Gebruiker niet gevonden.',
                ],
                404
            );
        }

        return $this->view(
            'users.show',
            [
                'title' => $gebruiker->fullName(),
                'titel' => 'Gebruiker',
                'gebruiker' => $gebruiker,
                'logs' => $this->auditLog->history(
                    'user',
                    $id
                ),
            ]
        );
    }

    public function create(): Response
    {
        return $this->view(
            'users.create',
            [
                'title' => 'Nieuwe gebruiker',
                'titel' => 'Nieuwe gebruiker',
                'leden' => $this->memberService->all(),
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
            $userRequest = new UserRequest($input);

            $id = $this->userService->create(
                $userRequest->all()
            );

            $this->success(
                'De gebruiker werd succesvol aangemaakt.'
            );

            return $this->redirect(
                '/users/' . $id
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
                'De gebruiker kon niet worden aangemaakt.'
            );

            return $this->redirect(
                '/users/create'
            );
        }
    }

    public function edit(): Response
    {
        $id = $this->routeId();

        $gebruiker = $this->userService->find($id);

        if ($gebruiker === null) {
            return $this->view(
                'core::errors.404',
                [
                    'message' => 'Gebruiker niet gevonden.',
                ],
                404
            );
        }

        return $this->view(
            'users.edit',
            [
                'title' => 'Gebruiker wijzigen',
                'titel' => 'Gebruiker wijzigen',
                'gebruiker' => $gebruiker,
                'leden' => $this->memberService->all(),
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
            $userRequest = new UserRequest($input);

            $this->userService->update(
                $id,
                $userRequest->all()
            );

            $this->success(
                'De gebruiker werd succesvol gewijzigd.'
            );

            return $this->redirect(
                '/users/' . $id
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
                'De gebruiker kon niet worden gewijzigd.'
            );

            return $this->redirect(
                '/users/' . $id . '/edit'
            );
        }
    }

    public function delete(): Response
    {
        $id = $this->routeId();

        $gebruiker = $this->userService->find($id);

        if ($gebruiker === null) {
            return $this->view(
                'core::errors.404',
                [
                    'message' => 'Gebruiker niet gevonden.',
                ],
                404
            );
        }

        try {
            $this->userService->delete($id);

            $this->success(
                'De gebruiker werd succesvol verwijderd.'
            );
        } catch (Throwable $throwable) {
            $this->error(
                $throwable->getMessage()
            );
        }

        return $this->redirect('/users');
    }

    private function routeId(): int
    {
        return (int) $this->request()->route(
            'id',
            0
        );
    }
}