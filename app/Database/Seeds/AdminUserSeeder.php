<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use CodeIgniter\Shield\Entities\User;
use CodeIgniter\Shield\Models\UserModel;

class AdminUserSeeder extends Seeder
{
    public function run()
    {
        // Carrega os serviços necessários
        $userModel = model('UserModel'); // Forma correta de carregar o model
        $auth = service('auth'); // Serviço de autenticação
        $authorization = service('authorization'); // Serviço de autorização

        // Verifica se o usuário admin já existe
        if ($userModel->where('username', 'admin')->first()) {
            echo "Usuário admin já existe.\n";
            return;
        }

        // Cria o usuário admin
        $user = new User([
            'username' => 'admin',
            'email'    => 'admin@example.com',
            'password' => 'senhaadmin123', // Altere para uma senha segura!
            'name'     => 'Administrador',
            'active'   => true,
        ]);

        // Salva o usuário no banco de dados
        try {
            $userModel->save($user);

            // Obtém o usuário com ID gerado
            $user = $userModel->findById($userModel->getInsertID());

            // Verifica se o grupo admin existe, se não, cria
            if (!$authorization->groupExists('admin')) {
                $authorization->createGroup('admin', [
                    'title' => 'Administrator',
                    'description' => 'Full system access'
                ]);
            }

            // Adiciona o usuário ao grupo admin
            $authorization->addUserToGroup($user->id, 'admin');

            echo "Usuário admin criado com sucesso!\n";
            echo "Email: admin@example.com\n";
            echo "Senha: senhaadmin123\n";
        } catch (\Exception $e) {
            echo "Erro ao criar usuário admin: " . $e->getMessage() . "\n";
        }
    }
}
