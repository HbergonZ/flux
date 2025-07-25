<?php

namespace App\Controllers;

use Config\AD;

class TesteLDAP extends BaseController
{
    public function index()
    {
        return view('teste_ldap_view');
    }

    public function testar()
    {
        // Carrega configurações
        $config = new AD();

        // Obter dados do formulário
        $username = $this->request->getPost('username');
        $password = $this->request->getPost('password');

        if (empty($username) || empty($password)) {
            return redirect()->back()->with('error', 'Usuário e senha são obrigatórios');
        }

        // 1. Teste conexão LDAP
        $ldapConn = ldap_connect($config->host, $config->port);
        if (!$ldapConn) {
            return redirect()->back()->with('error', "Falha ao conectar ao LDAP: " . $config->host);
        }

        ldap_set_option($ldapConn, LDAP_OPT_PROTOCOL_VERSION, 3);
        ldap_set_option($ldapConn, LDAP_OPT_REFERRALS, 0);
        ldap_set_option($ldapConn, LDAP_OPT_DEBUG_LEVEL, 7); // Ativa logs detalhados

        // Tentativa de bind
        $bind = @ldap_bind($ldapConn, $config->bindUser, $config->bindPassword);
        if (!$bind) {
            $error = ldap_error($ldapConn);
            ldap_close($ldapConn);
            return redirect()->back()->with('error', "Falha no bind com usuário de serviço. Erro: " . $error);
        }

        // 3. Busca pelo usuário
        $search = ldap_search(
            $ldapConn,
            $config->baseDn,
            "(sAMAccountName=$username)"
        );

        if ($search === false) {
            ldap_close($ldapConn);
            return redirect()->back()->with('error', "Erro na busca LDAP: " . ldap_error($ldapConn));
        }

        $entries = ldap_get_entries($ldapConn, $search);
        if ($entries['count'] === 0) {
            ldap_close($ldapConn);
            return redirect()->back()->with('error', "Usuário não encontrado no AD");
        }

        $userDn = $entries[0]['dn'];

        // 4. Teste autenticação do usuário
        $userBind = @ldap_bind($ldapConn, $userDn, $password);
        ldap_close($ldapConn);

        if (!$userBind) {
            return redirect()->back()->with('error', "Credenciais inválidas para: $username");
        }

        // Se chegou aqui, autenticação foi bem-sucedida
        $data = [
            'success' => true,
            'userDn' => $userDn,
            'userData' => $entries[0]
        ];

        return view('teste_ldap_view', $data);
    }
}
