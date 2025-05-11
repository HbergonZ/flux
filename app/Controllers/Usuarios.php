<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\Shield\Entities\User;
use CodeIgniter\Shield\Models\UserModel;

class Usuarios extends BaseController
{
    protected $userModel;
    protected $authGroups;
    protected $authIdentities;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->authGroups = config('AuthGroups')->groups ?? ['superadmin' => [], 'admin' => [], 'user' => []];
        $this->authIdentities = db_connect()->table('auth_identities');
    }

    public function index()
    {
        $users = $this->userModel->findAll();

        foreach ($users as $user) {
            $user->groups = $user->getGroups();
            $identity = $this->authIdentities->where('user_id', $user->id)->get()->getRow();
            if ($identity) {
                $user->email = $identity->secret;
            }
        }

        $groups = array_keys($this->authGroups);

        $data = [
            'users' => $users,
            'groups' => $groups
        ];

        $this->content_data['content'] = view('sys/gerenciar-usuarios', $data);
        return view('layout', $this->content_data);
    }

    public function filtrar()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(405)->setJSON(['success' => false, 'message' => 'Método não permitido']);
        }

        $username = $this->request->getPost('username');
        $group = $this->request->getPost('group');

        $builder = $this->userModel->builder();

        if (!empty($username)) {
            $builder->like('username', $username);
        }

        $users = $builder->get()->getResult();

        if (!empty($group)) {
            $filteredUsers = [];
            foreach ($users as $user) {
                if (in_array($group, $user->getGroups())) {
                    $filteredUsers[] = $user;
                }
            }
            $users = $filteredUsers;
        }

        foreach ($users as $user) {
            $user->groups = $user->getGroups();
            $identity = $this->authIdentities->where('user_id', $user->id)->get()->getRow();
            if ($identity) {
                $user->email = $identity->secret;
            }
        }

        return $this->response->setJSON([
            'success' => true,
            'data' => $users
        ]);
    }

    public function editar($id = null)
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(405)->setJSON(['success' => false, 'message' => 'Método não permitido']);
        }

        if (!$id) {
            return $this->response->setStatusCode(400)->setJSON(['success' => false, 'message' => 'ID não fornecido']);
        }

        $user = $this->userModel->find($id);
        if (!$user) {
            return $this->response->setStatusCode(404)->setJSON(['success' => false, 'message' => 'Usuário não encontrado']);
        }

        $identity = $this->authIdentities->where('user_id', $user->id)->get()->getRow();
        $email = $identity ? $identity->secret : '';

        $isSelfEdit = (auth()->user()->id == $user->id);

        $responseData = [
            'id' => $user->id,
            'username' => $user->username,
            'email' => $email,
            'active' => $user->active,
            'is_self_edit' => $isSelfEdit,
            'groups' => $user->getGroups()
        ];

        return $this->response->setJSON([
            'success' => true,
            'data' => $responseData
        ]);
    }

    public function atualizar()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(405)->setJSON(['success' => false, 'message' => 'Método não permitido']);
        }

        $id = $this->request->getPost('id');
        if (!$id) {
            return $this->response->setStatusCode(400)->setJSON(['success' => false, 'message' => 'ID não fornecido']);
        }

        $user = $this->userModel->find($id);
        if (!$user) {
            return $this->response->setStatusCode(404)->setJSON(['success' => false, 'message' => 'Usuário não encontrado']);
        }

        $isSelfEdit = (auth()->user()->id == $user->id);
        $currentIdentity = $this->authIdentities->where('user_id', $user->id)->get()->getRow();
        $currentEmail = $currentIdentity ? $currentIdentity->secret : '';

        $rules = [
            'username' => 'required|min_length[3]|max_length[30]|is_unique[users.username,id,' . $id . ']',
            'email' => 'required|valid_email'
        ];

        $newEmail = $this->request->getPost('email');
        if ($newEmail !== $currentEmail) {
            $rules['email'] .= '|is_unique[auth_identities.secret]';
        }

        if (!$this->validate($rules)) {
            return $this->response->setStatusCode(422)->setJSON([
                'success' => false,
                'message' => implode('<br>', $this->validator->getErrors())
            ]);
        }

        try {
            $user->fill([
                'username' => $this->request->getPost('username')
            ]);

            if ($newEmail !== $currentEmail) {
                $this->authIdentities->where('user_id', $user->id)
                    ->update(['secret' => $newEmail]);
            }

            $this->userModel->save($user);

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Usuário atualizado com sucesso!'
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Erro ao atualizar usuário: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => 'Erro ao atualizar usuário. Por favor, tente novamente.'
            ]);
        }
    }

    public function alterarGrupo()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(405)->setJSON(['success' => false, 'message' => 'Método não permitido']);
        }

        $id = $this->request->getPost('id');
        if (!$id) {
            return $this->response->setStatusCode(400)->setJSON(['success' => false, 'message' => 'ID não fornecido']);
        }

        $user = $this->userModel->find($id);
        if (!$user) {
            return $this->response->setStatusCode(404)->setJSON(['success' => false, 'message' => 'Usuário não encontrado']);
        }

        // Verificar se está tentando alterar a si mesmo
        if (auth()->user()->id == $user->id) {
            return $this->response->setStatusCode(403)->setJSON([
                'success' => false,
                'message' => 'Você não pode alterar seu próprio grupo'
            ]);
        }

        // Verificar se usuário é superadmin
        if (in_array('superadmin', $user->getGroups())) {
            return $this->response->setStatusCode(403)->setJSON([
                'success' => false,
                'message' => 'Você não pode alterar o grupo de um superadministrador'
            ]);
        }

        $group = $this->request->getPost('group');
        $groups = array_keys($this->authGroups);

        if (!$group || !in_array($group, $groups)) {
            return $this->response->setStatusCode(400)->setJSON([
                'success' => false,
                'message' => 'Grupo inválido'
            ]);
        }

        // Verificar permissões do usuário logado
        $loggedUser = auth()->user();

        // Se for admin tentando criar superadmin
        if ($loggedUser->inGroup('admin') && $group === 'superadmin') {
            return $this->response->setStatusCode(403)->setJSON([
                'success' => false,
                'message' => 'Administradores não podem atribuir o grupo superadmin'
            ]);
        }

        // Se for admin tentando alterar outro admin
        if ($loggedUser->inGroup('admin') && in_array('admin', $user->getGroups())) {
            return $this->response->setStatusCode(403)->setJSON([
                'success' => false,
                'message' => 'Administradores não podem alterar outros administradores'
            ]);
        }

        try {
            foreach ($user->getGroups() as $oldGroup) {
                $user->removeGroup($oldGroup);
            }
            $user->addGroup($group);

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Grupo do usuário alterado com sucesso!'
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Erro ao alterar grupo do usuário: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => 'Erro ao alterar grupo do usuário. Por favor, tente novamente.'
            ]);
        }
    }

    public function excluir()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(405)->setJSON(['success' => false, 'message' => 'Método não permitido']);
        }

        $id = $this->request->getPost('id');
        if (!$id) {
            return $this->response->setStatusCode(400)->setJSON(['success' => false, 'message' => 'ID não fornecido']);
        }

        $user = $this->userModel->find($id);
        if (!$user) {
            return $this->response->setStatusCode(404)->setJSON(['success' => false, 'message' => 'Usuário não encontrado']);
        }

        if (auth()->user()->id == $user->id) {
            return $this->response->setStatusCode(403)->setJSON([
                'success' => false,
                'message' => 'Você não pode excluir a si mesmo'
            ]);
        }

        if (in_array('superadmin', $user->getGroups())) {
            return $this->response->setStatusCode(403)->setJSON([
                'success' => false,
                'message' => 'Você não pode excluir um superadministrador'
            ]);
        }

        // Admin não pode excluir outro admin
        if (in_array('admin', $user->getGroups()) && auth()->user()->inGroup('admin')) {
            return $this->response->setStatusCode(403)->setJSON([
                'success' => false,
                'message' => 'Administradores não podem excluir outros administradores'
            ]);
        }

        try {
            $this->authIdentities->where('user_id', $user->id)->delete();
            $this->userModel->delete($user->id);

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Usuário excluído com sucesso!'
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Erro ao excluir usuário: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => 'Erro ao excluir usuário. Por favor, tente novamente.'
            ]);
        }
    }
}
