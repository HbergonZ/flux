<?php

namespace App\Controllers;

use Config\AD;

class TesteLDAP extends BaseController
{
    public function index()
    {
        // Carrega configurações
        $config = new AD();

        // Dados para teste (substitua por um usuário/senha válido do AD)
        $username = ''; // Ex: 'j.silva' (sem domínio)
        $password = '';

        // 1. Teste conexão LDAP
        $ldapConn = ldap_connect($config->host, $config->port);
        if (!$ldapConn) {
            die("Falha ao conectar ao LDAP: " . $config->host);
        }

        ldap_set_option($ldapConn, LDAP_OPT_PROTOCOL_VERSION, 3);
        ldap_set_option($ldapConn, LDAP_OPT_REFERRALS, 0);
        ldap_set_option($ldapConn, LDAP_OPT_DEBUG_LEVEL, 7); // Ativa logs detalhados

        // Tentativa de bind
        $bind = @ldap_bind($ldapConn, $config->bindUser, $config->bindPassword);
        if (!$bind) {
            $error = ldap_error($ldapConn);
            ldap_close($ldapConn);
            die("Falha no bind com usuário de serviço. Erro: " . $error);
        }

        // 3. Busca pelo usuário
        $search = ldap_search(
            $ldapConn,
            $config->baseDn,
            "(sAMAccountName=$username)"
        );

        if ($search === false) {
            ldap_close($ldapConn);
            die("Erro na busca LDAP: " . ldap_error($ldapConn));
        }

        $entries = ldap_get_entries($ldapConn, $search);
        if ($entries['count'] === 0) {
            ldap_close($ldapConn);
            die("Usuário não encontrado no AD");
        }

        $userDn = $entries[0]['dn'];

        // 4. Teste autenticação do usuário
        $userBind = @ldap_bind($ldapConn, $userDn, $password);
        ldap_close($ldapConn);

        if (!$userBind) {
            die("Credenciais inválidas para: $userDn");
        }

        echo "Autenticação LDAP bem-sucedida!<br>";
        echo "Usuário DN: $userDn<br>";
        echo "Dados: <pre>" . print_r($entries[0], true) . "</pre>";
    }
}
