<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddNameToUsersTable extends Migration
{
    public function up()
    {
        $this->forge->addColumn('users', [
            'name' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
                'after' => 'username'
            ]
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('users', 'name');
    }
}
