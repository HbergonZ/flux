<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddAuthSourceToUsers extends Migration
{
    public function up()
    {
        $this->forge->addColumn('users', [
            'auth_source' => [
                'type'       => 'VARCHAR',
                'constraint' => 15,
                'default'    => 'local',
                'null'       => false,
                'after'      => 'username'
            ]
        ]);

        // Atualiza usuários existentes (opcional)
        if (ENVIRONMENT !== 'production') {
            $this->db->table('users')->update(['auth_source' => 'local']);
        }
    }

    public function down()
    {
        $this->forge->dropColumn('users', 'auth_source');
    }
}
