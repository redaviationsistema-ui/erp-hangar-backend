<?php

namespace App\Observers;

use App\Support\Audit\AuditLogger;
use Illuminate\Database\Eloquent\Model;

class AuditableObserver
{
    public function created(Model $model): void
    {
        AuditLogger::forModelEvent($model, 'created');
    }

    public function updated(Model $model): void
    {
        if (count($model->getChanges()) === 0) {
            return;
        }

        AuditLogger::forModelEvent($model, 'updated');
    }

    public function deleted(Model $model): void
    {
        AuditLogger::forModelEvent($model, 'deleted');
    }
}
