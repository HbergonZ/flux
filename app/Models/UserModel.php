<?php

declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Shield\Models\UserModel as ShieldUserModel;
use CodeIgniter\Shield\Entities\User;
use CodeIgniter\Database\Exceptions\DataException;

class UserModel extends ShieldUserModel
{
    protected $table = 'users';
    protected $primaryKey = 'id';
    protected $returnType = 'CodeIgniter\Shield\Entities\User';

    protected $allowedFields = [
        'username',
        'name',
        'email',
        'password',
        'active',
        'last_active',
        'deleted_at',
        'status',
        'status_message',
        'auth_source',
    ];

    protected $validationRules = [
        'username' => 'permit_empty|regex_match[/^\d{11}$/]|is_unique[users.username,id,{id}]',
        'email' => 'permit_empty|valid_email|is_unique[users.email,id,{id}]',
        'password' => 'permit_empty|min_length[8]',
        'name' => 'permit_empty|max_length[255]',
        'auth_source' => 'permit_empty|in_list[local,ldap]',
    ];

    protected function initialize(): void
    {
        parent::initialize();

        $this->afterInsert[] = 'assignDefaultGroup';
    }

    /**
     * Encontra usuário pelo username (CPF)
     */
    public function findByUsername(string $username): ?User
    {
        return $this->where('username', $username)->first();
    }

    /**
     * Cria um novo usuário LDAP com os dados mínimos necessários
     */
    public function createLdapUser(string $username, array $ldapData): User
    {
        // Verifica se o usuário já existe
        if ($existingUser = $this->findByUsername($username)) {
            return $existingUser;
        }

        $userData = [
            'username' => $username,
            'name' => $ldapData['displayname'][0] ?? $ldapData['cn'][0] ?? $username,
            'email' => $ldapData['mail'][0] ?? "{$username}@empresa.com",
            'auth_source' => 'ldap',
            'active' => 1,
            'password' => bin2hex(random_bytes(16)), // Senha aleatória
        ];

        $userId = $this->insert($userData);

        if (!$userId) {
            throw new DataException('Falha ao criar usuário LDAP: ' . implode(' ', $this->errors()));
        }

        return $this->find($userId);
    }

    /**
     * Sincroniza dados do usuário LDAP
     */
    public function syncLdapUserData(User $user, array $ldapData): bool
    {
        $updateData = [];

        $ldapName = $ldapData['displayname'][0] ?? $ldapData['cn'][0] ?? null;
        if ($ldapName && $user->name !== $ldapName) {
            $updateData['name'] = $ldapName;
        }

        $ldapEmail = $ldapData['mail'][0] ?? null;
        if ($ldapEmail && $user->email !== $ldapEmail) {
            $updateData['email'] = $ldapEmail;
        }

        // Verifica se o usuário tem algum grupo
        $hasGroup = $this->db->table('auth_groups_users')
            ->where('user_id', $user->id)
            ->countAllResults() > 0;

        // Se não tiver grupo, atribui o grupo padrão
        if (!$hasGroup) {
            $this->addToDefaultGroup($user); // Agora passando o objeto User
        }

        if (!empty($updateData)) {
            return $this->update($user->id, $updateData);
        }

        return false;
    }

    /**
     * Callback para atribuir grupo padrão após inserção
     */
    protected function assignDefaultGroup(array $data)
    {
        if (!empty($data['id'])) {
            $user = $this->find($data['id']);
            if ($user) {
                $this->addToDefaultGroup($user);
            }
        }

        return $data;
    }

    /**
     * Sobrescreve o método de hash para evitar re-hash de senhas LDAP
     */
    protected function hashPassword(array $data): array
    {
        if (
            !isset($data['data']['password']) ||
            (isset($data['data']['auth_source']) && $data['data']['auth_source'] === 'ldap')
        ) {
            return $data;
        }

        return parent::hashPassword($data);
    }

    /**
     * Método alternativo para adicionar grupo por ID quando não temos o objeto User
     */
    public function addToDefaultGroupById(int $userId): void
    {
        $user = $this->find($userId);
        if ($user) {
            $this->addToDefaultGroup($user);
        }
    }
}
