<?php

declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Shield\Models\UserModel as ShieldUserModel;
use CodeIgniter\Shield\Entities\User;

class UserModel extends ShieldUserModel
{
    protected $table = 'users';
    protected $primaryKey = 'id';

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
    }

    /**
     * Cria um novo usuário LDAP com os dados mínimos necessários
     */
    public function createLdapUser(string $username, array $ldapData): User
    {
        // Primeiro verifica se o usuário já existe
        if ($existingUser = $this->findByUsername($username)) {
            return $existingUser;
        }

        $user = new User([
            'username' => $username,
            'name' => $ldapData['displayname'][0] ?? $ldapData['cn'][0] ?? $username,
            'email' => $ldapData['mail'][0] ?? "{$username}@empresa.com",
            'auth_source' => 'ldap',
            'active' => 1,
            'password' => bin2hex(random_bytes(16)), // Senha aleatória
        ]);

        // Usamos insert() em vez de save() para evitar problemas com retorno
        if (!$this->insert($user)) {
            throw new \RuntimeException('Falha ao criar usuário LDAP: ' . implode(' ', $this->errors()));
        }

        // Recupera o usuário recém-criado com todos os dados
        $newUser = $this->findByUsername($username);
        if (!$newUser) {
            throw new \RuntimeException('Falha ao recuperar usuário LDAP recém-criado');
        }

        return $newUser;
    }

    /**
     * Encontra usuário pelo username (CPF)
     */
    public function findByUsername(string $username): ?User
    {
        $result = $this->where('username', $username)->first();
        return $result ? new User($result) : null;
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

        if (!empty($updateData)) {
            return $this->update($user->id, $updateData);
        }

        return false;
    }
}
