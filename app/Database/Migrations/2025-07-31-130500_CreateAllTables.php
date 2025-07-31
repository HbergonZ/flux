<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAllTables extends Migration
{
    public function up()
    {
        // Tabela: eixos
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'nome' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
            ],
            'objetivo' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'perspectiva_estrategica' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'responsaveis' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'status' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
            ],
            'data_publicacao' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'data_criacao' => [
                'type' => 'TIMESTAMP',
                'null' => true,
            ],
            'data_atualizacao' => [
                'type' => 'TIMESTAMP',
                'null' => true,
            ],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->createTable('eixos');

        // Tabela: planos
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'nome' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
            ],
            'sigla' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
            ],
            'descricao' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'data_criacao' => [
                'type' => 'TIMESTAMP',
                'null' => true,
            ],
            'data_atualizacao' => [
                'type' => 'TIMESTAMP',
                'null' => true,
            ],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->createTable('planos');

        // Tabela: projetos
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'identificador' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
            ],
            'nome' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
            ],
            'descricao' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'metas' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'projeto_vinculado' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'priorizacao_gab' => [
                'type' => 'INT',
                'constraint' => 11,
                'default' => 0,
            ],
            'id_eixo' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
            'id_plano' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
            'responsaveis' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'status' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
            ],
            'data_criacao' => [
                'type' => 'TIMESTAMP',
                'null' => true,
            ],
            'data_atualizacao' => [
                'type' => 'TIMESTAMP',
                'null' => true,
            ],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addForeignKey('id_eixo', 'eixos', 'id', 'SET NULL', 'SET NULL');
        $this->forge->addForeignKey('id_plano', 'planos', 'id', 'SET NULL', 'SET NULL');
        $this->forge->createTable('projetos');

        // Tabela: etapas
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'nome' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
            ],
            'ordem' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => true,
            ],
            'id_projeto' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
            'data_criacao' => [
                'type' => 'TIMESTAMP',
                'null' => true,
            ],
            'data_atualizacao' => [
                'type' => 'TIMESTAMP',
                'null' => true,
            ],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addForeignKey('id_projeto', 'projetos', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('etapas');

        // Tabela: acoes
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'nome' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
            ],
            'descricao' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'responsavel' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'tempo_estimado_dias' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => true,
            ],
            'entrega_estimada' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'data_inicio' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'data_fim' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'status' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
            ],
            'ordem' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => true,
            ],
            'id_projeto' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
            'id_etapa' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
            'created_at' => [
                'type' => 'TIMESTAMP',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'TIMESTAMP',
                'null' => true,
            ],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addForeignKey('id_projeto', 'projetos', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('id_etapa', 'etapas', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('acoes');

        // Tabela: evidencias
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'descricao' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'link' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'tipo' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
            ],
            'nivel' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
            ],
            'id_nivel' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'created_by' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'evidencia' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'TIMESTAMP',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'TIMESTAMP',
                'null' => true,
            ],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->createTable('evidencias');

        // Tabela: indicadores
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'conteudo' => [
                'type' => 'TEXT',
            ],
            'descricao' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'nivel' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
            ],
            'id_nivel' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'created_by' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'created_at' => [
                'type' => 'TIMESTAMP',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'TIMESTAMP',
                'null' => true,
            ],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->createTable('indicadores');

        // Tabela: log_administrativo
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'id_usuario' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'tipo_operacao' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
            ],
            'entidade' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
            ],
            'id_entidade' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
            'id_excluido' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
            'nome_entidade' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'id_plano' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
            'id_projeto' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
            'id_etapa' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
            'id_acao' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
            'id_solicitacao' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
            'dados_antigos' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'dados_novos' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'justificativa' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'data_registro' => [
                'type' => 'TIMESTAMP',
                'null' => true,
            ],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->createTable('log_administrativo');

        // Tabela: responsaveis
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'nivel' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
            ],
            'nivel_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'usuario_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'created_at' => [
                'type' => 'TIMESTAMP',
                'null' => true,
            ],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addForeignKey('usuario_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('responsaveis');

        // Tabela: solicitacoes
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'nivel' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
            ],
            'id_solicitante' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'id_plano' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
            'id_projeto' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
            'id_etapa' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
            'id_acao' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
            'id_meta' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
            'tipo' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
            ],
            'dados_atuais' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'dados_alterados' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'justificativa_solicitante' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'justificativa_avaliador' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'status' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
            ],
            'data_solicitacao' => [
                'type' => 'TIMESTAMP',
                'null' => true,
            ],
            'data_avaliacao' => [
                'type' => 'TIMESTAMP',
                'null' => true,
            ],
            'id_avaliador' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->createTable('solicitacoes');

        // Tabela: system_settings
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'setting_key' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
            ],
            'setting_value' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'TIMESTAMP',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'TIMESTAMP',
                'null' => true,
            ],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey('setting_key');
        $this->forge->createTable('system_settings');
    }

    public function down()
    {
        // Ordem reversa para evitar problemas com chaves estrangeiras
        $this->forge->dropTable('system_settings');
        $this->forge->dropTable('solicitacoes');
        $this->forge->dropTable('responsaveis');
        $this->forge->dropTable('log_administrativo');
        $this->forge->dropTable('indicadores');
        $this->forge->dropTable('evidencias');
        $this->forge->dropTable('acoes');
        $this->forge->dropTable('etapas');
        $this->forge->dropTable('projetos');
        $this->forge->dropTable('planos');
        $this->forge->dropTable('eixos');
    }
}
