<?php

declare(strict_types=1);

namespace App\Controllers;

use AEFS\Core\Auth;
use AEFS\Core\Http\Request;
use AEFS\Core\Http\Response;
use AEFS\Core\View\ViewFactory;
use App\Services\ReportExcelExportService;
use App\Services\EventAccessService;
use App\Services\ReportService;

final class ReportController extends BaseController
{
    public function __construct(
        ViewFactory $views,
        Request $request,
        private readonly ReportService $service,
        private readonly EventAccessService $access,
        private readonly ReportExcelExportService $excelExport
    ) {
        parent::__construct($views, $request);
    }

    public function index(): Response
    {
        if (!$this->access->hasManagementAccess()) {
            return $this->forbidden();
        }

        return $this->view(
            'reports.index',
            [
                'title' => 'Rapporten',
            ]
        );
    }

    public function shiftAttendance(): Response
    {
        if (!$this->access->hasManagementAccess()) {
            return $this->forbidden();
        }

        $shiftId = max(
            0,
            (int) $this->request()->query->get('shift_id', 0)
        );
        $report = $shiftId > 0
            ? $this->service->shiftAttendance($shiftId)
            : null;

        if ($shiftId > 0 && $report === null) {
            return $this->view(
                'core::errors.404',
                [
                    'title' => 'Shift niet gevonden',
                    'message' => 'De gekozen shift bestaat niet.',
                ],
                404
            );
        }

        if (
            $report !== null
            && !$this->access->canManage($report['shift']->eventId)
        ) {
            return $this->forbidden();
        }

        return $this->view(
            'reports.shift-attendance',
            [
                'title' => 'Aanwezigheidslijst per shift',
                'shifts' => $this->service->shiftsForAttendance(),
                'selectedShiftId' => $shiftId,
                'shift' => $report['shift'] ?? null,
                'registrations' => $report['registrations'] ?? [],
            ]
        );
    }

    public function eventCompensation(): Response
    {
        if (!$this->access->hasManagementAccess()) {
            return $this->forbidden();
        }

        $eventId = max(
            0,
            (int) $this->request()->query->get('event_id', 0)
        );
        $groupKey = $this->selectedGroupKey();

        if ($eventId > 0 && !$this->access->canManage($eventId)) {
            return $this->forbidden();
        }
        $report = $eventId > 0
            ? $this->service->eventCompensation(
                $eventId,
                $groupKey
            )
            : null;

        if ($eventId > 0 && $report === null) {
            return $this->view(
                'core::errors.404',
                [
                    'title' => 'Evenement niet gevonden',
                    'message' => 'Het gekozen evenement bestaat niet.',
                ],
                404
            );
        }

        return $this->view(
            'reports.event-compensation',
            [
                'title' => 'Vrijwilligersvergoedingen per evenement',
                'events' => $this->service->eventsForCompensation(),
                'selectedEventId' => $eventId,
                'selectedGroupKey' => $report['selected_group_key']
                    ?? null,
                'report' => $report,
                'canExport' => Auth::isAdmin(),
            ]
        );
    }

    public function eventCompensationExport(): Response
    {
        $eventId = max(
            0,
            (int) $this->request()->query->get('event_id', 0)
        );
        $report = $eventId > 0
            ? $this->service->eventCompensation(
                $eventId,
                $this->selectedGroupKey(),
                true
            )
            : null;

        if ($report === null) {
            return $this->view(
                'core::errors.404',
                [
                    'title' => 'Evenement niet gevonden',
                    'message' => 'Het gekozen evenement bestaat niet.',
                ],
                404
            );
        }

        return (new Response())->download(
            $this->excelExport->filename($report),
            $this->excelExport->export($report),
            ReportExcelExportService::MIME_TYPE
        );
    }

    private function selectedGroupKey(): ?string
    {
        $value = trim(
            (string) $this->request()->query->get('groep', '')
        );

        if (
            preg_match('/^(?:group:\d+|ungrouped)$/', $value) !== 1
        ) {
            return null;
        }

        return $value;
    }

    private function forbidden(): Response
    {
        return $this->view(
            'core::errors.403',
            ['message' => 'Je hebt geen toegang tot dit rapport.'],
            403
        );
    }
}
