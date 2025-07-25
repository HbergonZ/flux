<?php

namespace App\Controllers;

use Config\AD;
use CodeIgniter\Controller;

class TesteLDAP extends Controller
{
    public function index()
    {
        return view('teste_ldap');
    }

    public function testar()
    {
        $username = $this->request->getPost('username');
        $password = $this->request->getPost('password');
        $config = config(AD::class);

        // 1. Conexão com o servidor LDAP
        $ldapConn = ldap_connect($config->host, $config->port);
        if (!$ldapConn) {
            return redirect()->back()->with('error', 'Falha ao conectar ao servidor LDAP');
        }

        ldap_set_option($ldapConn, LDAP_OPT_PROTOCOL_VERSION, 3);
        ldap_set_option($ldapConn, LDAP_OPT_REFERRALS, 0);

        // 2. Bind com usuário de serviço
        $bind = @ldap_bind($ldapConn, $config->bindUser, $config->bindPassword);
        if (!$bind) {
            $error = ldap_error($ldapConn);
            ldap_close($ldapConn);
            return redirect()->back()->with('error', "Falha no bind com usuário de serviço. Erro: " . $error);
        }

        // 3. Tenta buscar em cada base DN
        $found = false;
        $result = null;
        $baseDnFound = null;

        foreach ($config->baseDns as $baseDn) {
            $search = ldap_search(
                $ldapConn,
                $baseDn,
                "(sAMAccountName=$username)"
            );

            if ($search !== false) {
                $entries = ldap_get_entries($ldapConn, $search);
                if ($entries['count'] > 0) {
                    $found = true;
                    $result = $entries;
                    $baseDnFound = $baseDn;
                    break;
                }
            }
        }

        if (!$found) {
            ldap_close($ldapConn);
            return redirect()->back()->with('error', 'Usuário não encontrado em nenhuma base DN');
        }

        // 4. Tenta autenticar o usuário
        $userDn = $result[0]['dn'];
        $userBind = @ldap_bind($ldapConn, $userDn, $password);

        if (!$userBind) {
            $error = ldap_error($ldapConn);
            ldap_close($ldapConn);
            return redirect()->back()->with('error', "Falha na autenticação. Erro: " . $error);
        }

        // 5. Se chegou aqui, autenticação foi bem sucedida
        ldap_close($ldapConn);

        // Prepara os dados para a view
        $data = [
            'success' => true,
            'userDn' => $userDn,
            'userData' => [
                'nome' => $result[0]['displayname'][0] ?? $result[0]['cn'][0] ?? 'Não informado',
                'email' => $result[0]['mail'][0] ?? 'Não informado',
                'base_dn' => $baseDnFound
            ]
        ];

        return view('teste_ldap', $data);
    }
}
