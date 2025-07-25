<?php

namespace App\Authentication\Authenticators;

use CodeIgniter\Shield\Authentication\Authenticators\Session;
use CodeIgniter\Shield\Result;
use Config\AD;
use CodeIgniter\Shield\Entities\User;

class LDAPAuthenticator extends Session
{
    public function attempt(array $credentials): Result
    {
        // Carrega os modelos necessários
        $userModel = model('App\Models\UserModel');
        $identityModel = model('CodeIgniter\Shield\Models\UserIdentityModel');

        // Validação das credenciais
        if (empty($credentials['username']) || empty($credentials['password'])) {
            return new Result([
                'success' => false,
                'reason' => lang('Auth.badAttempt')
            ]);
        }

        $username = trim($credentials['username']);
        $password = $credentials['password'];

        // Autenticação LDAP
        $ldapResult = $this->validateLDAPCredentials($username, $password);

        if (!$ldapResult['success']) {
            log_message('error', 'Falha LDAP: ' . ($ldapResult['error'] ?? 'Erro desconhecido'));
            return new Result([
                'success' => false,
                'reason' => $ldapResult['error'] ?? lang('Auth.invalidCredential')
            ]);
        }

        try {
            // Limpa sessão existente antes de novo login
            if (session()->has('user')) {
                session()->remove(['user', 'logged_in']);
            }

            // Busca ou cria usuário
            $user = $userModel->findByUsername($username);

            if ($user === null) {
                $user = $this->createUserFromLDAP($username, $ldapResult['user_data']);
                log_message('info', "Novo usuário LDAP criado: {$username}");
            } else {
                // Sincroniza dados do LDAP
                $userModel->syncLdapUserData($user, $ldapResult['user_data']);
            }

            // Verifica se usuário está ativo
            if (!$user->active) {
                return new Result([
                    'success' => false,
                    'reason' => lang('Auth.inactiveUser')
                ]);
            }

            // Cria identidade LDAP se não existir
            $identity = $identityModel->where('user_id', $user->id)
                ->where('type', 'ldap_identity')
                ->first();

            if (!$identity) {
                $this->createLDAPIdentity($user->id, $username);
            }

            // Realiza o login via sessão
            auth('session')->login($user);

            // Atualiza último acesso
            $user->last_active = date('Y-m-d H:i:s');
            $userModel->save($user);

            return new Result([
                'success' => true,
                'extraInfo' => $user
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Erro LDAP: ' . $e->getMessage());
            return new Result([
                'success' => false,
                'reason' => 'Erro durante a autenticação. Tente novamente.'
            ]);
        }
    }

    protected function validateLDAPCredentials(string $username, string $password): array
    {
        $config = config(AD::class);

        if (!function_exists('ldap_connect')) {
            return ['success' => false, 'error' => 'Extensão LDAP não está instalada no PHP'];
        }

        $ldapConn = ldap_connect($config->host, $config->port);

        if (!$ldapConn) {
            log_message('error', 'Falha ao conectar ao servidor LDAP: ' . $config->host);
            return ['success' => false, 'error' => 'Erro de conexão com o servidor'];
        }

        ldap_set_option($ldapConn, LDAP_OPT_PROTOCOL_VERSION, 3);
        ldap_set_option($ldapConn, LDAP_OPT_REFERRALS, 0);

        try {
            // Bind com conta de serviço
            $bind = @ldap_bind($ldapConn, $config->bindUser, $config->bindPassword);
            if (!$bind) {
                log_message('error', 'Falha no bind LDAP: ' . ldap_error($ldapConn));
                return ['success' => false, 'error' => 'Erro de autenticação'];
            }

            // Busca usuário por CPF (sAMAccountName)
            $search = ldap_search($ldapConn, $config->baseDn, "(sAMAccountName={$username})");
            if ($search === false) {
                return ['success' => false, 'error' => 'Erro na busca LDAP'];
            }

            $entries = ldap_get_entries($ldapConn, $search);
            if ($entries['count'] === 0) {
                return ['success' => false, 'error' => 'Usuário não encontrado'];
            }

            $userDn = $entries[0]['dn'];

            // Valida credenciais do usuário
            $userBind = @ldap_bind($ldapConn, $userDn, $password);
            if (!$userBind) {
                return ['success' => false, 'error' => 'Credenciais inválidas'];
            }

            return [
                'success' => true,
                'user_data' => $entries[0]
            ];
        } catch (\Exception $e) {
            log_message('error', 'Erro LDAP: ' . $e->getMessage());
            return ['success' => false, 'error' => 'Erro durante a autenticação'];
        } finally {
            if ($ldapConn) {
                ldap_unbind($ldapConn);
            }
        }
    }

    protected function createUserFromLDAP(string $username, array $ldapData): User
    {
        // Gera uma senha aleatória para o campo password (requerido pelo Shield)
        $randomPassword = bin2hex(random_bytes(16));

        $user = new User([
            'username' => $username,
            'email' => $ldapData['mail'][0] ?? "{$username}@empresa.com",
            'name' => $ldapData['displayname'][0] ?? $ldapData['cn'][0] ?? $username,
            'auth_source' => 'ldap',
            'active' => 1,
            'password' => $randomPassword
        ]);

        // Primeiro salvamos o usuário
        if (!$this->provider->save($user)) {
            throw new \RuntimeException('Falha ao criar usuário: ' . implode(' ', $this->provider->errors()));
        }

        // Depois recuperamos o usuário pelo username
        $savedUser = $this->provider->findByCredentials(['username' => $username]);
        if (!$savedUser) {
            throw new \RuntimeException('Falha ao recuperar usuário recém-criado');
        }

        // Atribui o grupo padrão 'user' ao novo usuário LDAP
        $this->assignDefaultGroup($savedUser->id);

        // Cria a identidade LDAP
        $this->createLDAPIdentity($savedUser->id, $username);

        return $savedUser;
    }

    protected function assignDefaultGroup(int $userId): void
    {
        try {
            $db = \Config\Database::connect();
            $builder = $db->table('auth_groups_users');

            // Verifica se o usuário já tem o grupo
            $exists = $builder->where('user_id', $userId)
                ->where('group', 'user')
                ->countAllResults();

            if ($exists === 0) {
                $builder->insert([
                    'user_id' => $userId,
                    'group' => 'user', // Grupo padrão
                    'created_at' => date('Y-m-d H:i:s')
                ]);
            }
        } catch (\Exception $e) {
            log_message('error', 'Erro ao atribuir grupo padrão: ' . $e->getMessage());
            throw $e;
        }
    }

    protected function createLDAPIdentity(int $userId, string $username): void
    {
        try {
            $identities = model('CodeIgniter\Shield\Models\UserIdentityModel');

            if (!$identities) {
                throw new \RuntimeException('Falha ao carregar o modelo de identidades');
            }

            $existing = $identities->where('user_id', $userId)
                ->where('type', 'ldap_identity')
                ->first();

            if ($existing) {
                return;
            }

            $result = $identities->insert([
                'user_id' => $userId,
                'type' => 'ldap_identity',
                'name' => $username,
                'secret' => $username,
                'secret2' => null,
                'extra' => json_encode(['auth_source' => 'ldap']),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            if (!$result) {
                throw new \RuntimeException('Falha ao criar identidade LDAP: ' . implode(' ', $identities->errors()));
            }
        } catch (\Exception $e) {
            log_message('error', 'Erro ao criar identidade LDAP: ' . $e->getMessage());
            throw $e;
        }
    }

    protected function syncUserData(User $user, array $ldapData): void
    {
        $updateData = [];

        // Sincroniza nome se diferente
        $ldapName = $ldapData['displayname'][0] ?? $ldapData['cn'][0] ?? null;
        if ($ldapName && $user->name !== $ldapName) {
            $updateData['name'] = $ldapName;
        }

        // Sincroniza email se diferente
        $ldapEmail = $ldapData['mail'][0] ?? null;
        if ($ldapEmail && $user->email !== $ldapEmail) {
            $updateData['email'] = $ldapEmail;
        }

        if (!empty($updateData)) {
            $this->provider->update($user->id, $updateData);
        }
    }

    protected function findUser(string $username): ?User
    {
        // Busca pela identidade LDAP
        $identity = model('AuthIdentitiesModel')
            ->where('type', 'ldap_identity')
            ->where('name', $username)
            ->first();

        if ($identity) {
            return $this->provider->findById($identity->user_id);
        }

        return null;
    }
}
