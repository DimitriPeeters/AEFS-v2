<?php

declare(strict_types=1);

namespace AEFS\Controllers;

use AEFS\Http\Requests\MemberRequest;
use AEFS\Services\AuditLogService;
use AEFS\Services\MemberService;
use Throwable;

final class MemberController extends BaseController
{
    public function __construct(
        private MemberService $service,
        private AuditLogService $auditLog
    ) {
        parent::__construct();
    }

    public function index(): void
    {
        $zoekterm = trim(
            (string) $this->request->query('zoek', '')
        );

        $leden = $zoekterm === ''
            ? $this->service->all()
            : $this->service->search($zoekterm);

        $this->view(
            'members.index',
            [
                'title'     => 'Leden',
                'titel'     => 'Leden',
                'zoekterm'  => $zoekterm,
                'leden'     => $leden,
            ]
        );
    }

    public function show(): void
    {
        $id = (int) $this->request->route('id');

        $lid = $this->service->find($id);

        if ($lid === null) {

            http_response_code(404);

            exit('Lid niet gevonden.');

        }

        $this->view(
            'members.show',
            [
                'title' => $lid->fullName(),
                'titel' => 'Ledenfiche',
                'lid'   => $lid,
                'logs'  => $this->auditLog->history(
                    'member',
                    $id
                ),
            ]
        );
    }

    public function create(): void
    {
        $this->view(
            'members.create',
            [
                'title' => 'Nieuw lid',
                'titel' => 'Nieuw lid',
            ]
        );
    }

    public function store(): void
    {
        try {

            $request = new MemberRequest(
                $this->request->all()
            );

            $id = $this->service->create(
                $request->all()
            );

            header(
                'Location: /members/' . $id
            );

            exit;

        } catch (Throwable $e) {

            $this->view(
                'members.create',
                [
                    'title'  => 'Nieuw lid',
                    'titel'  => 'Nieuw lid',
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

        $lid = $this->service->find($id);

        if ($lid === null) {

            http_response_code(404);

            exit('Lid niet gevonden.');

        }

        $this->view(
            'members.edit',
            [
                'title' => $lid->fullName(),
                'titel' => 'Lid wijzigen',
                'lid'   => $lid,
            ]
        );
    }

    public function update(): void
    {
        $id = (int) $this->request->route('id');

        try {

            $request = new MemberRequest(
                $this->request->all()
            );

            $this->service->update(
                $id,
                $request->all()
            );

            header(
                'Location: /members/' . $id
            );

            exit;

        } catch (Throwable $e) {

            $this->view(
                'members.edit',
                [
                    'title'  => 'Lid wijzigen',
                    'titel'  => 'Lid wijzigen',
                    'lid'    => $this->service->find($id),
                    'errors' => [
                        $e->getMessage(),
                    ],
                ]
            );

        }
    }

    public function delete(): void
    {
        $id = (int) $this->request->route('id');

        $this->service->delete($id);

        header('Location: /members');

        exit;
    }
}