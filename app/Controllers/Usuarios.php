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
        $this->authGroups = config('AuthGroups')->groups;
    }

    public function index()
    {

        $users = $this->userModel->findAll();

        // Carrega os grupos para cada usuário
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
            return redirect()->back();
        }

        $username = $this->request->getPost('username');
        $group = $this->request->getPost('group');

        $builder = $this->userModel->builder();

        if (!empty($username)) {
            $builder->like('username', $username);
        }

        $users = $builder->get()->getResult();

        // Filtra por grupo se necessário
        if (!empty($group)) {
            $filteredUsers = [];
            foreach ($users as $user) {
                if (in_array($group, $user->getGroups())) {
                    $filteredUsers[] = $user;
                }
            }
            $users = $filteredUsers;
        }

        // Carrega os grupos para cada usuário
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
            return redirect()->back();
        }

        $user = $this->userModel->find($id);
        if (!$user) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Usuário não encontrado'
            ]);
        }

        // Verifica se é admin e não pode editar outro admin
        if (in_array('admin', $user->getGroups()) && auth()->user()->id != $user->id) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Você não pode editar outro administrador'
            ]);
        }

        return $this->response->setJSON([
            'success' => true,
            'data' => $user
        ]);
    }

    public function atualizar()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        $id = $this->request->getPost('id');
        $user = $this->userModel->find($id);

        if (!$user) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Usuário não encontrado'
            ]);
        }

        // Verifica se é admin e não pode editar outro admin
        if (in_array('admin', $user->getGroups()) && auth()->user()->id != $user->id) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Você não pode editar outro administrador'
            ]);
        }

        $rules = [
            'username' => 'required|min_length[3]|max_length[30]|is_unique[users.username,id,' . $id . ']',
            'email' => 'required|valid_email|is_unique[users.email,id,' . $id . ']',
        ];

        if (!$this->validate($rules)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => implode('<br>', $this->validator->getErrors())
            ]);
        }

        try {
            $user->fill($this->request->getPost());
            $this->userModel->save($user);

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Usuário atualizado com sucesso!'
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Erro ao atualizar usuário: ' . $e->getMessage()
            ]);
        }
    }

    public function alterarGrupo()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        $id = $this->request->getPost('id');
        $user = $this->userModel->find($id);

        if (!$user) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Usuário não encontrado'
            ]);
        }

        // Verifica se é admin e não pode editar outro admin
        if (in_array('admin', $user->getGroups()) && auth()->user()->id != $user->id) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Você não pode alterar o grupo de outro administrador'
            ]);
        }

        $group = $this->request->getPost('group');
        $groups = array_keys($this->authGroups);

        if (!in_array($group, $groups)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Grupo inválido'
            ]);
        }

        try {
            // Remove todos os grupos e adiciona o novo
            foreach ($user->getGroups() as $oldGroup) {
                $user->removeGroup($oldGroup);
            }
            $user->addGroup($group);

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Grupo do usuário alterado com sucesso!'
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Erro ao alterar grupo: ' . $e->getMessage()
            ]);
        }
    }

    public function excluir()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        $id = $this->request->getPost('id');
        $user = $this->userModel->find($id);

        if (!$user) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Usuário não encontrado'
            ]);
        }

        // Verifica se é admin e não pode excluir outro admin
        if (in_array('admin', $user->getGroups())) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Você não pode excluir um administrador'
            ]);
        }

        // Não pode excluir a si mesmo
        if (auth()->user()->id == $user->id) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Você não pode excluir a si mesmo'
            ]);
        }

        try {
            $this->userModel->delete($user->id);

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Usuário excluído com sucesso!'
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Erro ao excluir usuário: ' . $e->getMessage()
            ]);
        }
    }
}
