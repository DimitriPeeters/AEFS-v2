<?php

declare(strict_types=1);

namespace App\Controllers;

use AEFS\Core\Auth;
use AEFS\Core\Config;
use AEFS\Core\Http\JsonResponse;
use AEFS\Core\Http\Request;
use AEFS\Core\Http\Response;
use AEFS\Core\Http\UploadedFile;
use AEFS\Core\Session;
use AEFS\Core\View\Helper\CsrfHelper;
use AEFS\Core\View\ViewFactory;
use App\Http\Requests\MailingRequest;
use App\Models\Mailing;
use App\Services\EventAccessService;
use App\Services\MailQueueProcessor;
use App\Services\MailService;
use DomainException;
use Throwable;

final class MailController extends BaseController
{
    public function __construct(
        ViewFactory $views,
        Request $request,
        private readonly MailService $service,
        private readonly EventAccessService $access,
        private readonly CsrfHelper $csrf,
        private readonly Config $config,
        private readonly MailQueueProcessor $queueProcessor
    ) {
        parent::__construct($views, $request);
    }

    public function index(): Response
    {
        if (!$this->access->hasManagementAccess()) {
            return $this->forbidden();
        }

        $eventIds = $this->access->managedEventIds();

        return $this->view(
            'mailings.index',
            [
                'title' => 'Mailings',
                'mailings' => Auth::isAdmin()
                    ? $this->service->latest()
                    : $this->service->latestForEvents(
                        $eventIds,
                        Auth::id() ?? 0
                    ),
                'totals' => Auth::isAdmin()
                    ? $this->service->totals()
                    : $this->service->totalsForEvents(
                        $eventIds,
                        Auth::id() ?? 0
                    ),
                'mailConfigured' => $this->mailConfigured(),
                'smtpHost' => (string) $this->config->get(
                    'mail.host',
                    ''
                ),
                'recipientRestriction' => $this->service
                    ->recipientRestriction(),
            ]
        );
    }

    public function processScheduledQueue(): Response
    {
        try {
            $result = $this->queueProcessor->process();

            return new JsonResponse(
                [
                    'success' => true,
                    'message' => 'De mailwachtrij werd verwerkt.',
                    'processed' => $result['processed'],
                    'sent' => $result['sent'],
                    'failed' => $result['failed'],
                ],
                200,
                $this->schedulerResponseHeaders()
            );
        } catch (Throwable $throwable) {
            error_log(sprintf(
                'AEFS mailworker schedulerfout (%s).',
                $throwable::class
            ));

            return new JsonResponse(
                [
                    'success' => false,
                    'message' => 'De mailwachtrij kon niet worden verwerkt.',
                ],
                503,
                $this->schedulerResponseHeaders()
            );
        }
    }

    public function create(): Response
    {
        if (!$this->access->hasManagementAccess()) {
            return $this->forbidden();
        }

        $requestedEventId = max(
            0,
            (int) $this->request()->query->get('event_id', 0)
        );

        if (
            $requestedEventId > 0
            && !$this->access->canManage($requestedEventId)
        ) {
            return $this->forbidden();
        }

        return $this->view(
            'mailings.create',
            [
                'title' => 'Nieuwe mailing',
                'options' => Auth::isAdmin()
                    ? $this->service->audienceOptions()
                    : $this->service->audienceOptionsForEvents(
                        $this->access->managedEventIds()
                    ),
                'eventManagerMode' => !Auth::isAdmin(),
                'selectedEventIds' => $requestedEventId > 0
                    ? [$requestedEventId]
                    : [],
                'mailConfigured' => $this->mailConfigured(),
                'recipientRestriction' => $this->service
                    ->recipientRestriction(),
            ]
        );
    }

    public function store(): Response
    {
        $input = $this->request()->request->all();

        try {
            $this->validateCsrf($input);
            $request = new MailingRequest($input);
            $data = $request->all();
            $attachment = $this->request()->file('bijlage');

            if (!Auth::isAdmin()) {
                $eventIds = $data['event_ids'];

                if (
                    $data['doelgroep_type'] !== 'evenement'
                    || count($eventIds) !== 1
                ) {
                    throw new DomainException(
                        'Een eventbeheerder kan uitsluitend deelnemers van één beheerd evenement mailen.'
                    );
                }

                $this->access->requireManage($eventIds[0]);
            }

            if (is_array($attachment)) {
                throw new DomainException(
                    'Er kan per mailing maximaal één bijlage worden toegevoegd.'
                );
            }

            $mailingId = $this->service->queueManual(
                $data,
                $attachment instanceof UploadedFile ? $attachment : null,
                $this->requireUserId()
            );

            $this->success(
                'De mailing werd gepersonaliseerd en in de verzendwachtrij geplaatst.'
            );

            return $this->redirect('/mailings/' . $mailingId);
        } catch (Throwable $throwable) {
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
            $this->error(
                'De mailing kon niet worden ingepland.'
            );

            return $this->redirect('/mailings/create');
        }
    }

    public function show(): Response
    {
        $mailingId = $this->routeId();
        $mailing = $this->service->find($mailingId);

        if ($mailing === null) {
            return $this->view(
                'core::errors.404',
                [
                    'title' => 'Mailing niet gevonden',
                    'message' => 'De gevraagde mailing bestaat niet.',
                ],
                404
            );
        }

        if (!$this->canAccessMailing($mailing)) {
            return $this->forbidden();
        }

        return $this->view(
            'mailings.show',
            [
                'title' => $mailing->subject,
                'mailing' => $mailing,
                'recipients' => $this->service->recipients($mailingId),
                'recipientRestriction' => $this->service
                    ->recipientRestriction(),
            ]
        );
    }

    public function retry(): Response
    {
        $mailingId = $this->routeId();
        $mailing = $this->service->find($mailingId);

        if ($mailing === null) {
            return $this->view(
                'core::errors.404',
                [
                    'title' => 'Mailing niet gevonden',
                    'message' => 'De gevraagde mailing bestaat niet.',
                ],
                404
            );
        }

        if (!$this->canAccessMailing($mailing)) {
            return $this->forbidden();
        }

        $input = $this->request()->request->all();

        try {
            $this->validateCsrf($input);
            $count = $this->service->retryFailed(
                $mailingId,
                $this->requireUserId()
            );

            $this->success(
                $count > 0
                    ? sprintf(
                        '%d mislukte ontvanger(s) werden opnieuw ingepland.',
                        $count
                    )
                    : 'Er waren geen mislukte ontvangers om opnieuw in te plannen.'
            );
        } catch (Throwable $throwable) {
            $this->error($throwable->getMessage());
        }

        return $this->redirect('/mailings/' . $mailingId);
    }

    public function sendShiftPlanning(): Response
    {
        $eventId = $this->routeId();

        if (!$this->access->canManage($eventId)) {
            return $this->forbidden();
        }

        $input = $this->request()->request->all();

        try {
            $this->validateCsrf($input);
            $mailingId = $this->service->queueShiftPlanning(
                $eventId,
                $this->requireUserId()
            );

            $this->success(
                'De persoonlijke shiftoverzichten werden in de verzendwachtrij geplaatst.'
            );

            return $this->redirect('/mailings/' . $mailingId);
        } catch (Throwable $throwable) {
            $this->error($throwable->getMessage());

            return $this->redirect('/events/' . $eventId);
        }
    }

    /**
     * @param array<string, mixed> $input
     */
    private function validateCsrf(array $input): void
    {
        $token = $input['_token'] ?? null;

        if (!is_string($token) || !$this->csrf->validate($token)) {
            throw new DomainException(
                'Ongeldige of verlopen beveiligingstoken.'
            );
        }
    }

    private function requireUserId(): int
    {
        $userId = Auth::id();

        if ($userId === null || $userId <= 0) {
            throw new DomainException(
                'Je moet aangemeld zijn om deze actie uit te voeren.'
            );
        }

        return $userId;
    }

    private function routeId(): int
    {
        return (int) $this->request()->route('id', 0);
    }

    private function mailConfigured(): bool
    {
        return (bool) $this->config->get('mail.enabled', false)
            && trim((string) $this->config->get('mail.host', '')) !== ''
            && trim((string) $this->config->get('mail.username', '')) !== ''
            && trim((string) $this->config->get('mail.password', '')) !== ''
            && filter_var(
                $this->config->get('mail.from_address', ''),
                FILTER_VALIDATE_EMAIL
            ) !== false;
    }

    private function canAccessMailing(Mailing $mailing): bool
    {
        return Auth::isAdmin()
            || (
                $mailing->eventId !== null
                && $mailing->type === 'manueel'
                && $mailing->audienceType === 'evenement'
                && $mailing->createdBy === Auth::id()
                && $this->access->canManage($mailing->eventId)
            );
    }

    private function forbidden(): Response
    {
        return $this->view(
            'core::errors.403',
            [
                'message' => 'Je hebt geen toegang tot deze mailing.',
            ],
            403
        );
    }

    /**
     * @return array<string, string>
     */
    private function schedulerResponseHeaders(): array
    {
        return [
            'Cache-Control' => 'no-store, private',
            'X-Content-Type-Options' => 'nosniff',
        ];
    }
}
