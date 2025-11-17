<?php

namespace Src\Controllers;

use Src\Repositories\UserRepository;
use Src\Validation\Validator;

class UserController extends BaseController
{
    public function index()
    {
        $page = (int)($_GET['page'] ?? 1);
        $perPage = (int)($_GET['per_page'] ?? 10);
        $search = $_GET['search'] ?? '';
        $sortBy = $_GET['sort_by'] ?? 'id';
        $sortDir = $_GET['sort_dir'] ?? 'DESC';

        $repository = new UserRepository($this->cfg);
        $this->ok($repository->paginate(
            max(1, $page),
            min(100, max(1, $perPage)),
            $search,
            $sortBy,
            $sortDir
        ));
    }

    public function show($id)
    {
        $repository = new UserRepository($this->cfg);
        $user = $repository->find((int)$id);

        if ($user) {
            $this->ok($user);
        } else {
            $this->error(404, 'User not found');
        }
    }

    public function store()
    {
        $input = json_decode(file_get_contents('php://input'), true) ?? [];
        $validator = Validator::make($input, [
            'name' => 'required|min:3|max:100',
            'email' => 'required|email|max:150',
            'password' => 'required|min:6|max:72',
            'role' => 'enum:user,admin'
        ]);

        if ($validator->fails()) {
            return $this->error(422, 'Validation error', $validator->errors());
        }

        $passwordHash = password_hash($input['password'], PASSWORD_DEFAULT);
        $repository = new UserRepository($this->cfg);

        try {
            $this->ok(
                $repository->create(
                    $input['name'],
                    $input['email'],
                    $passwordHash,
                    $input['role'] ?? 'user'
                ),
                201
            );
        } catch (\Throwable $exception) {
            $this->error(400, 'Create failed', ['details' => $exception->getMessage()]);
        }
    }

    public function update($id)
    {
        $input = json_decode(file_get_contents('php://input'), true) ?? [];
        $validator = Validator::make($input, [
            'name' => 'required|min:3|max:100',
            'email' => 'required|email|max:150',
            'role' => 'enum:user,admin'
        ]);

        if ($validator->fails()) {
            return $this->error(422, 'Validation error', $validator->errors());
        }

        $repository = new UserRepository($this->cfg);
        $this->ok($repository->update((int)$id, $input['name'], $input['email'], $input['role']));
    }

    public function destroy($id)
    {
        $repository = new UserRepository($this->cfg);
        $isDeleted = $repository->delete((int)$id);

        if ($isDeleted) {
            $this->ok(['deleted' => true]);
        } else {
            $this->error(400, 'Delete failed');
        }
    }
}