<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class AD extends BaseConfig
{
    public string $host;
    public int $port;
    public string $baseDn;
    public string $bindUser;
    public string $bindPassword;
    public int $timeout;
    public int $version;

    public function __construct()
    {
        $this->host = env('LDAP_HOST', '172.16.0.45');
        $this->port = env('LDAP_PORT', 389);
        $this->baseDn = env('LDAP_BASE_DN', 'OU=SETIC_RO,OU=EXECUTIVO,DC=rondonia,DC=local');
        $this->bindUser = env('LDAP_BIND_USER', 'fluxsystem');
        $this->bindPassword = env('LDAP_BIND_PASSWORD', '');
        $this->timeout = env('LDAP_TIMEOUT', 10);
        $this->version = env('LDAP_VERSION', 3);
    }
}
