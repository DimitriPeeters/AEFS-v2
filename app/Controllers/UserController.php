<?php

declare(strict_types=1);

namespace AEFS\Controllers;

use AEFS\Http\Requests\UserRequest;
use AEFS\Services\AuditLogService;
use AEFS\Services\MemberService;
use AEFS\Services\UserService;
use Throwable;

final class UserController extends BaseController
{
    public function __construct(
        private UserService $userService,
        private MemberService $memberService,
        private AuditLogService $auditLog
    ) {
        parent::__construct();
    }

    public function index(): void
    {
        $zoekterm = trim(
            (string) $this->request->query('zoek', '')
        );

        $gebruikers = $zoekterm === ''
            ? $this->userService->all()
            : $this->userService->search($zoekterm);

        $this->view(
            'users.index',
            [
                'title'       => 'Gebruikers',
                'titel'       => 'Gebruikers',
                'zoekterm'    => $zoekterm,
                'gebruikers'  => $gebruikers,
            ]
        );
    }

    public function show(): void
    {
        $id = (int) $this->request->route('id');

        $gebruiker = $this->userService->find($id);

        if ($gebruiker === null) {

            http_response_code(404);

            exit('Gebruiker niet gevonden.');

        }

        $this->view(
            'users.show',
            [
                'title'      => $gebruiker->fullName(),
                'titel'      => 'Gebruiker',
                'gebruiker'  => $gebruiker,
                'logs'       => $this->auditLog->history(
                    'user',
                    $id
                ),
            ]
        );
    }

    public function create(): void
    {
        $this->view(
            'users.create',
            [
                'title' => 'Nieuwe gebruiker',
                'titel' => 'Nieuwe gebruiker',
                'leden' => $this->memberService->all(),
            ]
        );
    }

    public function store(): void
    {
        try {

            $request = new UserRequest(
                $this->request->all()
            );

            $id = $this->userService->create(
                $request->all()
            );

            header('Location: /users/' . $id);

            exit;

        } catch (Throwable $e) {

            $this->view(
                'users.create',
                [
                    'title'  => 'Nieuwe gebruiker',
                    'titel'  => 'Nieuwe gebruiker',
                    'leden'  => $this->memberService->all(),
                    'errors' => [
                        $e->getMessage(),
                    ],
                ]
            );

        }
    }

    public function edit(): void
    {
        $id = (int) $this->request->route('id');

        $gebruiker = $this->userService->find($id);

        if ($gebruiker === null) {

            http_response_code(404);

            exit('Gebruiker niet gevonden.');

        }

        $this->view(
            'users.edit',
            [
                'title'      => 'Gebruiker wijzigen',
                'titel'      => 'Gebruiker wijzigen',
                'gebruiker'  => $gebruiker,
                'leden'      => $this->memberService->all(),
            ]
        );
    }

    public function update(): void
    {
        $id = (int) $this->request->route('id');

        try {

            $request = new UserRequest(
                $this->request->all()
            );

            $this->userService->update(
                $id,
                $request->all()
            );

            header('Location: /users/' . $id);

            exit;

        } catch (Throwable $e) {

            $this->view(
                'users.edit',
                [
                    'title'      => 'Gebruiker wijzigen',
                    'titel'      => 'Gebruiker wijzigen',
                    'gebruiker'  => $this->userService->find($id),
                    'leden'      => $this->memberService->all(),
                    'errors'     => [
                        $e->getMessage(),
                    ],
                ]
            );

        }
    }

    public function delete(): void
    {
        $id = (int) $this->request->route('id');

        $this->userService->delete($id);

        header('Location: /users');

        exit;
    }
}