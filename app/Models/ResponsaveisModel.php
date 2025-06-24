<?php

namespace App\Models;

use CodeIgniter\Model;

class ResponsaveisModel extends Model
{
    protected $table = 'responsaveis';
    protected $primaryKey = 'id';
    protected $allowedFields = ['nivel', 'nivel_id', 'usuario_id', 'created_at'];
    protected $useTimestamps = false;

    public function getResponsaveis($nivel, $nivelId)
    {
        return $this->select('responsaveis.*, users.username, auth_identities.secret as email')
            ->join('users', 'users.id = responsaveis.usuario_id')
            ->join('auth_identities', 'auth_identities.user_id = users.id AND auth_identities.type = "email_password"', 'left')
            ->where('nivel', $nivel)
            ->where('nivel_id', $nivelId)
            ->orderBy('created_at', 'ASC')
            ->findAll();
    }

    public function adicionarResponsavel($nivel, $nivelId, $usuarioId)
    {
        // Verifica se já existe
        $exists = $this->where('nivel', $nivel)
            ->where('nivel_id', $nivelId)
            ->where('usuario_id', $usuarioId)
            ->first();

        if ($exists) {
            return false;
        }

        return $this->insert([
            'nivel' => $nivel,
            'nivel_id' => $nivelId,
            'usuario_id' => $usuarioId,
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }

    public function removerResponsavel($nivel, $nivelId, $usuarioId)
    {
        return $this->where('nivel', $nivel)
            ->where('nivel_id', $nivelId)
            ->where('usuario_id', $usuarioId)
            ->delete();
    }

    public function getUsuariosDisponiveis($nivel, $nivelId)
    {
        // Primeiro obtemos os IDs dos usuários que já são responsáveis
        $responsaveis = $this->where('nivel', $nivel)
            ->where('nivel_id', $nivelId)
            ->findAll();

        $idsResponsaveis = array_column($responsaveis, 'usuario_id');

        // Agora buscamos todos os usuários ativos que não estão na lista de responsáveis
        $builder = $this->db->table('users')
            ->select('users.id, users.username, auth_identities.secret as email')
            ->join('auth_identities', 'auth_identities.user_id = users.id AND auth_identities.type = "email_password"', 'left')
            ->where('users.active', 1);

        // Se houver responsáveis, adicionamos a condição NOT IN
        if (!empty($idsResponsaveis)) {
            $builder->whereNotIn('users.id', $idsResponsaveis);
        }

        return $builder->orderBy('username', 'ASC')
            ->get()
            ->getResultArray();
    }
}
