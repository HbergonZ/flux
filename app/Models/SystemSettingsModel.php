<?php

namespace App\Models;

use CodeIgniter\Model;

class SystemSettingsModel extends Model
{
    protected $table = 'system_settings';
    protected $primaryKey = 'id';
    protected $allowedFields = ['setting_key', 'setting_value'];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    public function getSetting($key, $default = null)
    {
        $setting = $this->where('setting_key', $key)->first();
        return $setting ? $setting['setting_value'] : $default;
    }

    public function setSetting($key, $value)
    {
        return $this->updateOrInsert(['setting_key' => $key], ['setting_value' => $value]);
    }
}
