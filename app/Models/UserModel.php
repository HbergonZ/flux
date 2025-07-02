<?php

declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Shield\Models\UserModel as ShieldUserModel;

class UserModel extends ShieldUserModel
{
    protected $table = 'users';
    protected $primaryKey = 'id';

    protected $allowedFields = [
        'name',
        'email',
        'password',
        'active',
        'last_active',
        'deleted_at',
        'status',
        'status_message',
        // Adicione outros campos personalizados aqui
    ];

    protected function initialize(): void
    {
        parent::initialize();

        // Configurações adicionais podem ser feitas aqui
        $this->afterInsert[] = 'afterInsert';
        $this->afterUpdate[] = 'afterUpdate';
    }

    /**
     * Executado após inserir um novo usuário
     */
    protected function afterInsert(array $data): void
    {
        // Lógica pós-criação do usuário, se necessário
    }

    /**
     * Executado após atualizar um usuário
     */
    protected function afterUpdate(array $data): void
    {
        // Lógica pós-atualização do usuário, se necessário
    }

    /**
     * Localiza um usuário pelo email
     */
    public function findByEmail(string $email): ?object
    {
        return $this->where('email', $email)->first();
    }

    /**
     * Atualiza a data de última atividade
     */
    public function updateLastActive(int $userId): void
    {
        $this->update($userId, ['last_active' => date('Y-m-d H:i:s')]);
    }

    /**
     * Sobrescreve o método de busca por credenciais
     * para usar apenas email (sem username)
     */
    public function findByIdentity(string $identity): ?object
    {
        // Busca apenas por email
        return $this->where('email', $identity)->first();
    }
}
