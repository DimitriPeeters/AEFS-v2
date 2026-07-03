<?php

declare(strict_types=1);

namespace AEFS\Controllers;

use AEFS\Core\Flash;
use AEFS\Core\Request;
use AEFS\Core\Response;
use AEFS\Services\UserService;
use AEFS\Validators\UserValidator;

final class UserController
{
    public function __construct(
        private UserService $users,
        private UserValidator $validator
    ) {
    }

    public function index(Request $request): void
    {
        $zoekterm = trim((string)$request->query('q', ''));

        $gebruikers = $zoekterm === ''
            ? $this->users->all()
            : $this->users->search($zoekterm);

        $title = 'Gebruikers';

        require dirname(__DIR__, 2) . '/resources/views/users/index.php';
    }

    public function show(Request $request): void
    {
        $id = (int)$request->route('id');

        $gebruiker = $this->users->find($id);

        if ($gebruiker === null) {
            Response::notFound();
        }

        $title = 'Gebruiker';

        require dirname(__DIR__, 2) . '/resources/views/users/show.php';
    }

    public function create(): void
    {
        $gebruiker = null;

        $title = 'Nieuwe gebruiker';

        require dirname(__DIR__, 2) . '/resources/views/users/create.php';
    }

    public function store(Request $request): never
    {
        $data = [

            'lid_id' => (int)$request->post('lid_id'),

            'email' => trim((string)$request->post('email')),

            'rol' => trim((string)$request->post('rol')),

            'actief' => $request->post('actief') ? 1 : 0,

            'mail_blacklist' => $request->post('mail_blacklist') ? 1 : 0,

            'wachtwoord_moet_wijzigen' => $request->post('wachtwoord_moet_wijzigen') ? 1 : 0,

            'password' => (string)$request->post('password'),

        ];

        $errors = $this->validator->validate($data);

        if ($errors !== []) {

            foreach ($errors as $error) {
                Flash::error($error);
            }

            Response::redirect('/gebruikers/nieuw');
        }

        $this->users->create($data);

        Flash::success('Gebruiker succesvol toegevoegd.');

        Response::redirect('/gebruikers');
    }

    public function edit(Request $request): void
    {
        $gebruiker = $this->users->find(
            (int)$request->route('id')
        );

        if ($gebruiker === null) {
            Response::notFound();
        }

        $title = 'Gebruiker bewerken';

        require dirname(__DIR__, 2) . '/resources/views/users/edit.php';
    }

    public function update(Request $request): never
    {
        $id = (int)$request->route('id');

        $data = $_POST;

        $errors = $this->validator->validate($data);

        if ($errors !== []) {

            foreach ($errors as $error) {
                Flash::error($error);
            }

            Response::redirect('/gebruikers/' . $id . '/bewerken');
        }

        $this->users->update($id, $data);

        Flash::success('Gebruiker succesvol bijgewerkt.');

        Response::redirect('/gebruikers');
    }

    public function delete(Request $request): never
    {
        $this->users->delete(
            (int)$request->route('id')
        );

        Flash::success('Gebruiker verwijderd.');

        Response::redirect('/gebruikers');
    }
}