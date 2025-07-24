<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddAuthSourceToUsers extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();
        $fields = $db->getFieldData('users');
        $columnExists = false;

        foreach ($fields as $field) {
            if ($field->name === 'name') {
                $columnExists = true;
                break;
            }
        }

        if (!$columnExists) {
            $this->forge->addColumn('users', [
                'name' => [
                    'type' => 'VARCHAR',
                    'constraint' => 200,
                    'null' => true,
                    'after' => 'username'
                ]
            ]);
        }
    }

    public function down()
    {
        //
    }
}