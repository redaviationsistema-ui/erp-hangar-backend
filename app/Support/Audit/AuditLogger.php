<?php

namespace App\Support\Audit;

use App\Models\Aeronave;
use App\Models\AuditLog;
use App\Models\Consumible;
use App\Models\Discrepancia;
use App\Models\Herramienta;
use App\Models\Medicion;
use App\Models\Motor;
use App\Models\Ndt;
use App\Models\Orden;
use App\Models\Refaccion;
use App\Models\TallerExterno;
use App\Models\Tarea;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Throwable;

class AuditLogger
{
    public static function log(string $action, string $description, array $payload = []): ?AuditLog
    {
        if (! self::auditTableReady()) {
            return null;
        }

        $request = request();

        return AuditLog::query()->create([
            'user_id' => $payload['user_id'] ?? Auth::id(),
            'action' => $action,
            'entity_type' => $payload['entity_type'] ?? 'sistema',
            'entity_id' => $payload['entity_id'] ?? null,
            'entity_label' => $payload['entity_label'] ?? null,
            'order_id' => $payload['order_id'] ?? null,
            'description' => $description,
            'old_values' => self::sanitizeValue($payload['old_values'] ?? null),
            'new_values' => self::sanitizeValue($payload['new_values'] ?? null),
            'context' => self::sanitizeValue($payload['context'] ?? null),
            'ip_address' => $payload['ip_address'] ?? $request?->ip(),
            'user_agent' => $payload['user_agent'] ?? $request?->userAgent(),
            'occurred_at' => $payload['occurred_at'] ?? now(),
        ]);
    }

    public static function forModelEvent(Model $model, string $event): void
    {
        $action = self::resolveAction($model, $event);
        $description = self::buildDescription($model, $action);

        self::log($action, $description, [
            'entity_type' => self::entityType($model),
            'entity_id' => $model->getKey(),
            'entity_label' => self::entityLabel($model),
            'order_id' => self::resolveOrderId($model),
            'old_values' => self::oldValues($model, $event),
            'new_values' => self::newValues($model, $event),
            'context' => self::context($model, $event),
        ]);
    }

    public static function entityType(Model|string $model): string
    {
        $class = is_string($model) ? $model : $model::class;

        return Str::snake(class_basename($class));
    }

    private static function resolveAction(Model $model, string $event): string
    {
        if ($event === 'created') {
            return 'created';
        }

        if ($event === 'deleted') {
            return 'deleted';
        }

        if ($model instanceof Orden && $model->wasChanged('estado')) {
            $old = Str::lower((string) $model->getOriginal('estado'));
            $new = Str::lower((string) $model->getAttribute('estado'));

            if ($new === 'cerrada') {
                return 'closed';
            }

            if ($old === 'cerrada' && $new !== 'cerrada') {
                return 'reopened';
            }

            if (in_array($new, ['aprobada', 'aprobado', 'liberada', 'liberado'], true)) {
                return 'approved';
            }
        }

        return 'updated';
    }

    private static function buildDescription(Model $model, string $action): string
    {
        $label = self::entityLabel($model);
        $entity = self::humanEntityName($model);

        return match ($action) {
            'created' => "Se creo {$entity} {$label}.",
            'updated' => "Se actualizo {$entity} {$label}.",
            'closed' => "Se cerro {$entity} {$label}.",
            'reopened' => "Se reabrio {$entity} {$label}.",
            'approved' => "Se aprobo {$entity} {$label}.",
            'deleted' => "Se elimino {$entity} {$label}.",
            default => "Se registro {$action} en {$entity} {$label}.",
        };
    }

    private static function humanEntityName(Model $model): string
    {
        return match (self::entityType($model)) {
            'orden' => 'la orden',
            'aeronave' => 'la aeronave',
            'motor' => 'el motor',
            'tarea' => 'la tarea',
            'discrepancia' => 'la discrepancia',
            'refaccion' => 'la refaccion',
            'consumible' => 'el consumible',
            'herramienta' => 'la herramienta',
            'ndt' => 'el registro NDT',
            'taller_externo' => 'el taller externo',
            'medicion' => 'la medicion',
            default => 'el registro',
        };
    }

    private static function entityLabel(Model $model): string
    {
        return match (true) {
            $model instanceof Orden => $model->folio ?: "#{$model->getKey()}",
            $model instanceof Aeronave => $model->matricula ?: "#{$model->getKey()}",
            $model instanceof Motor => $model->numero_serie ?: "#{$model->getKey()}",
            $model instanceof Tarea => $model->titulo ?: "#{$model->getKey()}",
            $model instanceof Discrepancia => $model->item ?: Str::limit((string) $model->descripcion, 40, ''),
            $model instanceof Refaccion => $model->numero_parte ?: ($model->descripcion ?: "#{$model->getKey()}"),
            $model instanceof Consumible => $model->nombre ?: ($model->descripcion ?: "#{$model->getKey()}"),
            $model instanceof Herramienta => $model->nombre ?: ($model->descripcion ?: "#{$model->getKey()}"),
            $model instanceof Ndt => $model->tipo_prueba ?: "#{$model->getKey()}",
            $model instanceof TallerExterno => $model->proveedor ?: ($model->descripcion ?: "#{$model->getKey()}"),
            $model instanceof Medicion => $model->parametro ?: ($model->descripcion ?: "#{$model->getKey()}"),
            default => "#{$model->getKey()}",
        };
    }

    private static function resolveOrderId(Model $model): ?int
    {
        if ($model instanceof Orden) {
            return $model->getKey();
        }

        $orderId = $model->getAttribute('orden_id');

        return is_numeric($orderId) ? (int) $orderId : null;
    }

    private static function oldValues(Model $model, string $event): ?array
    {
        if ($event === 'created') {
            return null;
        }

        if ($event === 'deleted') {
            return self::sanitizeArray($model->getOriginal());
        }

        $changes = [];
        foreach (array_keys($model->getChanges()) as $key) {
            if (in_array($key, ['updated_at'], true)) {
                continue;
            }

            $changes[$key] = $model->getOriginal($key);
        }

        return self::sanitizeArray($changes);
    }

    private static function newValues(Model $model, string $event): ?array
    {
        if ($event === 'deleted') {
            return null;
        }

        if ($event === 'created') {
            return self::sanitizeArray($model->getAttributes());
        }

        return self::sanitizeArray(Arr::except($model->getChanges(), ['updated_at']));
    }

    private static function context(Model $model, string $event): array
    {
        $context = [
            'event' => $event,
            'model' => $model::class,
        ];

        if ($model instanceof Orden) {
            $context['estado'] = $model->estado;
            $context['area_id'] = $model->area_id;
        }

        if ($orderId = self::resolveOrderId($model)) {
            $context['orden_id'] = $orderId;
        }

        return $context;
    }

    private static function sanitizeValue(mixed $value): mixed
    {
        if ($value === null) {
            return null;
        }

        if (is_array($value)) {
            return self::sanitizeArray($value);
        }

        if (is_string($value)) {
            return self::sanitizeString($value);
        }

        return $value;
    }

    private static function sanitizeArray(array $values): array
    {
        $sanitized = [];

        foreach ($values as $key => $value) {
            if (in_array($key, ['password', 'remember_token'], true)) {
                continue;
            }

            if (is_array($value)) {
                $sanitized[$key] = self::sanitizeArray($value);
                continue;
            }

            if (is_string($value)) {
                $sanitized[$key] = self::sanitizeString($value);
                continue;
            }

            $sanitized[$key] = $value;
        }

        return $sanitized;
    }

    private static function sanitizeString(string $value): string
    {
        $trimmed = trim($value);

        if (preg_match('/^[A-Za-z0-9+\/=]{200,}$/', $trimmed) === 1) {
            return '[omitted-base64]';
        }

        return strlen($trimmed) > 400 ? Str::limit($trimmed, 400, '...') : $trimmed;
    }

    private static function auditTableReady(): bool
    {
        try {
            return Schema::hasTable('audit_logs');
        } catch (Throwable) {
            return false;
        }
    }
}
