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
        $userModel = model('App\Models\UserModel');
        $identityModel = model('CodeIgniter\Shield\Models\UserIdentityModel');

        if (empty($credentials['username']) || empty($credentials['password'])) {
            return new Result([
                'success' => false,
                'reason' => lang('Auth.badAttempt')
            ]);
        }

        $username = trim($credentials['username']);
        $password = $credentials['password'];

        $ldapResult = $this->validateLDAPCredentials($username, $password);

        if (!$ldapResult['success']) {
            log_message('error', 'Falha LDAP: ' . ($ldapResult['error'] ?? 'Erro desconhecido'));
            return new Result([
                'success' => false,
                'reason' => $ldapResult['error'] ?? lang('Auth.invalidCredential')
            ]);
        }

        try {
            if (session()->has('user')) {
                session()->remove(['user', 'logged_in']);
            }

            $user = $userModel->findByUsername($username);

            if ($user === null) {
                $user = $this->createUserFromLDAP($username, $ldapResult['user_data']);
                log_message('info', "Novo usuário LDAP criado: {$username}");
            } else {
                $userModel->syncLdapUserData($user, $ldapResult['user_data']);
            }

            if (!$user->active) {
                return new Result([
                    'success' => false,
                    'reason' => lang('Auth.inactiveUser')
                ]);
            }

            $identity = $identityModel->where('user_id', $user->id)
                ->where('type', 'ldap_identity')
                ->first();

            if (!$identity) {
                $this->createLDAPIdentity($user->id, $username, $ldapResult['user_data']);
            }

            auth('session')->login($user);

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

        // Tenta cada base DN até encontrar o usuário
        foreach ($config->baseDns as $baseDn) {
            $result = $this->tryLdapConnection($username, $password, [
                'host' => $config->host,
                'port' => $config->port,
                'baseDn' => $baseDn,
                'bindUser' => $config->bindUser,
                'bindPassword' => $config->bindPassword
            ]);

            if ($result['success']) {
                return $result;
            }
        }

        return ['success' => false, 'error' => 'Usuário não encontrado em nenhuma base DN'];
    }

    protected function tryLdapConnection(string $username, string $password, array $config): array
    {
        $ldapConn = ldap_connect("ldap://{$config['host']}:{$config['port']}");

        if (!$ldapConn) {
            log_message('error', 'Falha ao conectar ao servidor LDAP: ' . $config['host']);
            return ['success' => false, 'error' => 'Erro de conexão com o servidor'];
        }

        ldap_set_option($ldapConn, LDAP_OPT_PROTOCOL_VERSION, 3);
        ldap_set_option($ldapConn, LDAP_OPT_REFERRALS, 0);

        try {
            $bind = @ldap_bind($ldapConn, $config['bindUser'], $config['bindPassword']);
            if (!$bind) {
                log_message('error', 'Falha no bind LDAP: ' . ldap_error($ldapConn));
                return ['success' => false, 'error' => 'Erro de autenticação'];
            }

            $search = ldap_search($ldapConn, $config['baseDn'], "(sAMAccountName={$username})");
            if ($search === false) {
                return ['success' => false, 'error' => 'Erro na busca LDAP'];
            }

            $entries = ldap_get_entries($ldapConn, $search);
            if ($entries['count'] === 0) {
                return ['success' => false, 'error' => 'Usuário não encontrado'];
            }

            $userDn = $entries[0]['dn'];

            $userBind = @ldap_bind($ldapConn, $userDn, $password);
            if (!$userBind) {
                return ['success' => false, 'error' => 'Credenciais inválidas'];
            }

            return [
                'success' => true,
                'user_data' => $entries[0]
            ];
        } finally {
            if ($ldapConn) {
                ldap_unbind($ldapConn);
            }
        }
    }

    protected function createUserFromLDAP(string $username, array $ldapData): User
    {
        $userModel = model('App\Models\UserModel');

        $userData = [
            'username' => $username,
            'name' => $ldapData['displayname'][0] ?? $ldapData['cn'][0] ?? $username,
            'auth_source' => 'ldap',
            'active' => 1,
        ];

        // Desativa a validação para campos que podem não existir
        $userId = $userModel->insert($userData);

        if (!$userId) {
            throw new DataException('Falha ao criar usuário LDAP: ' . implode(' ', $userModel->errors()));
        }

        return $userModel->find($userId);
    }

    protected function createLDAPIdentity(int $userId, string $username, array $ldapData = []): void
    {
        $identities = model('CodeIgniter\Shield\Models\UserIdentityModel');

        $email = $ldapData['mail'][0] ?? null;

        $identities->insert([
            'user_id' => $userId,
            'type' => 'email_password', // Tipo usado pelo Shield para credenciais de email
            'name' => $email ?: 'ldap_user', // Usa o email se existir
            'secret' => $email ?: $username, // Armazena o email ou username no secret
            'secret2' => null,
            'extra' => json_encode(['auth_source' => 'ldap']),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        // Adiciona também a identidade LDAP específica
        $identities->insert([
            'user_id' => $userId,
            'type' => 'ldap_identity',
            'name' => $username,
            'secret' => $username,
            'secret2' => null,
            'extra' => json_encode(['auth_source' => 'ldap']),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }
}
