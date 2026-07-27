<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Redirect extends Model
{
    protected $table = 'redirects';

    protected $fillable = ['von', 'nach', 'status', 'notiz'];

    protected function casts(): array
    {
        return ['zuletzt_genutzt_at' => 'datetime'];
    }

    /** Zählt mit, ob eine Regel überhaupt greift — wichtig für die Kontrolle nach dem Go-Live. */
    public function benutzt(): void
    {
        $this->newQuery()
            ->whereKey($this->getKey())
            ->update([
                'treffer' => $this->getConnection()->raw('treffer + 1'),
                'zuletzt_genutzt_at' => now(),
            ]);
    }
}
