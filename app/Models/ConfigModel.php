<?php

namespace App\Models;

use Config\Database as DatabaseConfig;

class ConfigModel extends TenantModel
{
    protected $DBGroup          = 'default';
    protected $table            = 'ta_configuracion';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['clave', 'valor'];

    protected $useTimestamps = false;

    private function defaultValues(): array
    {
        return [
            'texto_cierre' => "Aviso importante\n\n"
                . "Queremos informarles que el dia <fecha> las canchas permaneceran cerradas.\n"
                . "Pedimos disculpas por las molestias que esto pueda ocasionar.\n\n"
                . "De todas formas, ya pueden reservar normalmente las horas para fechas posteriores.\n"
                . "Muchas gracias por la comprension y por seguir eligiendonos.",
            'email_reservas' => '',
        ];
    }

    public function getValue(string $key, ?string $fallback = null): string
    {
        $row = $this->where('clave', $key)->first();
        $value = is_array($row) ? (string) ($row['valor'] ?? '') : '';
        if (trim($value) !== '') {
            return $value;
        }

        // Fallback: si estamos en DB de tenant y no hay valor, usar configuracion global.
        try {
            $databaseConfig = config(DatabaseConfig::class);
            $defaultConnection = \Config\Database::connect($databaseConfig->default);
            if ($defaultConnection->tableExists($this->table)) {
                $defaultRow = $defaultConnection->table($this->table)->where('clave', $key)->get()->getRowArray();
                $defaultValue = is_array($defaultRow) ? (string) ($defaultRow['valor'] ?? '') : '';
                if (trim($defaultValue) !== '') {
                    return $defaultValue;
                }
            }
        } catch (\Throwable $e) {
            // Si falla el fallback global, devolvemos fallback local.
        }

        if ($fallback !== null) {
            return $fallback;
        }

        $defaults = $this->defaultValues();
        return (string) ($defaults[$key] ?? '');
    }
}
