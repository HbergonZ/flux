<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\Shield\Entities\User;
use CodeIgniter\Shield\Models\UserModel;

class Usuarios extends BaseController
{
    protected $userModel;
    protected $authGroups;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->authGroups = config('AuthGroups')->groups ?? ['admin' => [], 'user' => []];
    }

    public function index()
    {
        $users = $this->userModel->findAll();

        foreach ($users as $user) {
            $user->groups = $user->getGroups();
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

        $isSelfEdit = (auth()->user()->id == $user->id);

        $responseData = [
            'id' => $user->id,
            'username' => $user->username,
            'email' => $user->email,
            'active' => $user->active,
            'is_self_edit' => $isSelfEdit
        ];

        if (!$isSelfEdit) {
            $responseData['groups'] = $user->getGroups();
        }

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

        $rules = [
            'username' => 'required|min_length[3]|max_length[30]|is_unique[users.username,id,' . $id . ']',
            'email' => 'required|valid_email|is_unique[users.email,id,' . $id . ']'
        ];

        if (!$isSelfEdit) {
            $rules['group'] = 'required|in_list[' . implode(',', array_keys($this->authGroups)) . ']';
        }

        if (!$this->validate($rules)) {
            return $this->response->setStatusCode(422)->setJSON([
                'success' => false,
                'message' => implode('<br>', $this->validator->getErrors())
            ]);
        }

        try {
            $user->fill([
                'username' => $this->request->getPost('username'),
                'email' => $this->request->getPost('email')
            ]);

            if (!$isSelfEdit) {
                foreach ($user->getGroups() as $oldGroup) {
                    $user->removeGroup($oldGroup);
                }
                $user->addGroup($this->request->getPost('group'));
            }

            $this->userModel->save($user);

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Usuário atualizado com sucesso!'
            ]);
        } catch (\Exception $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => 'Erro ao atualizar usuário: ' . $e->getMessage()
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

        if (in_array('admin', $user->getGroups()) && auth()->user()->id != $user->id) {
            return $this->response->setStatusCode(403)->setJSON(['success' => false, 'message' => 'Você não pode alterar o grupo de outro administrador']);
        }

        $group = $this->request->getPost('group');
        $groups = array_keys($this->authGroups);

        if (!$group || !in_array($group, $groups)) {
            return $this->response->setStatusCode(400)->setJSON([
                'success' => false,
                'message' => 'Grupo inválido'
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
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => 'Erro ao alterar grupo: ' . $e->getMessage()
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

        if (in_array('admin', $user->getGroups())) {
            return $this->response->setStatusCode(403)->setJSON(['success' => false, 'message' => 'Você não pode excluir um administrador']);
        }

        if (auth()->user()->id == $user->id) {
            return $this->response->setStatusCode(403)->setJSON(['success' => false, 'message' => 'Você não pode excluir a si mesmo']);
        }

        try {
            $this->userModel->delete($user->id);

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Usuário excluído com sucesso!'
            ]);
        } catch (\Exception $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => 'Erro ao excluir usuário: ' . $e->getMessage()
            ]);
        }
    }
}
