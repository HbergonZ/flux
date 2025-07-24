<?php

declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Shield\Models\UserModel as ShieldUserModel;

class UserModel extends ShieldUserModel
{
    protected $table = 'users';
    protected $primaryKey = 'id';

    protected $allowedFields = [
        'username', // Para armazenar o CPF
        'name',
        'email',
        'password',
        'active',
        'last_active',
        'deleted_at',
        'status',
        'status_message',
        'auth_source', // 'local' ou 'ldap'
    ];

    protected $validationRules = [
        'username' => 'permit_empty|regex_match[/^\d{11}$/]|is_unique[users.username,id,{id}]',
        'email' => 'permit_empty|valid_email|is_unique[users.email,id,{id}]',
        'password' => 'permit_empty|min_length[8]',
        // ... outras regras do Shield
    ];

    protected function initialize(): void
    {
        parent::initialize();
        $this->afterInsert[] = 'afterInsert';
    }
    protected function afterInsert(array $data): void
    {
        if (isset($data['data']['auth_source']) && $data['data']['auth_source'] === 'ldap') {
            $this->createLDAPIdentity($data['id'], $data['data']['username']);
        }
    }

    protected function createLDAPIdentity(int $userId, string $username): void
    {
        // Carrega o model corretamente
        $db = \Config\Database::connect();

        $db->table('auth_identities')->insert([
            'user_id' => $userId,
            'type' => 'ldap',
            'name' => $username,
            'secret' => null, // Campo obrigatório no Shield
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }

    protected function afterUpdate(array $data): void
    {
        // Lógica pós-atualização
    }

    public function findByEmail(string $email): ?object
    {
        return $this->where('email', $email)->first();
    }

    public function findByCPF(string $cpf): ?object
    {
        return $this->where('username', $cpf)->first();
    }

    public function findByIdentity(string $identity): ?object
    {
        if (filter_var($identity, FILTER_VALIDATE_EMAIL)) {
            return $this->where('email', $identity)->first();
        }
        return $this->where('username', $identity)->first();
    }

    public function updateLastActive(int $userId): void
    {
        $this->update($userId, ['last_active' => date('Y-m-d H:i:s')]);
    }
}
