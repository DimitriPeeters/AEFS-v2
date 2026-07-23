<?php

declare(strict_types=1);

namespace App\Controllers;

use AEFS\Core\Http\Request;
use AEFS\Core\Http\Response;
use AEFS\Core\Session;
use AEFS\Core\View\ViewFactory;
use AEFS\Http\Requests\MemberRequest;
use App\Services\AuditLogService;
use App\Services\MemberService;
use Throwable;

final class MemberController extends BaseController
{
    public function __construct(
        ViewFactory $views,
        Request $request,
        private readonly MemberService $service,
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

        $leden = $zoekterm === ''
            ? $this->service->all()
            : $this->service->search($zoekterm);

        return $this->view(
            'members.index',
            [
                'title' => 'Leden',
                'titel' => 'Leden',
                'zoekterm' => $zoekterm,
                'leden' => $leden,
            ]
        );
    }

    public function show(): Response
    {
        $id = $this->routeId();

        $lid = $this->service->find($id);

        if ($lid === null) {
            return $this->view(
                'core::errors.404',
                [
                    'message' => 'Lid niet gevonden.',
                ],
                404
            );
        }

        return $this->view(
            'members.show',
            [
                'title' => $lid->fullName(),
                'titel' => 'Ledenfiche',
                'lid' => $lid,
                'logs' => $this->auditLog->history(
                    'member',
                    $id
                ),
            ]
        );
    }

    public function create(): Response
    {
        return $this->view(
            'members.create',
            [
                'title' => 'Nieuw lid',
                'titel' => 'Nieuw lid',
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
            $memberRequest = new MemberRequest($input);

            $id = $this->service->create(
                $memberRequest->all()
            );

            $this->success(
                'Het lid werd succesvol aangemaakt.'
            );

            return $this->redirect(
                '/members/' . $id
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
                'Het lid kon niet worden aangemaakt.'
            );

            return $this->redirect(
                '/members/create'
            );
        }
    }

    public function edit(): Response
    {
        $id = $this->routeId();

        $lid = $this->service->find($id);

        if ($lid === null) {
            return $this->view(
                'core::errors.404',
                [
                    'message' => 'Lid niet gevonden.',
                ],
                404
            );
        }

        return $this->view(
            'members.edit',
            [
                'title' => $lid->fullName(),
                'titel' => 'Lid wijzigen',
                'lid' => $lid,
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
            $memberRequest = new MemberRequest($input);

            $this->service->update(
                $id,
                $memberRequest->all()
            );

            $this->success(
                'Het lid werd succesvol gewijzigd.'
            );

            return $this->redirect(
                '/members/' . $id
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
                'Het lid kon niet worden gewijzigd.'
            );

            return $this->redirect(
                '/members/' . $id . '/edit'
            );
        }
    }

    public function delete(): Response
    {
        $id = $this->routeId();

        $lid = $this->service->find($id);

        if ($lid === null) {
            return $this->view(
                'core::errors.404',
                [
                    'message' => 'Lid niet gevonden.',
                ],
                404
            );
        }

        try {
            $this->service->delete($id);

            $this->success(
                'Het lid werd succesvol verwijderd.'
            );
        } catch (Throwable $throwable) {
            $this->error(
                $throwable->getMessage()
            );
        }

        return $this->redirect('/members');
    }

    private function routeId(): int
    {
        return (int) $this->request()->route(
            'id',
            0
        );
    }
}