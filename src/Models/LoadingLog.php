<?php

namespace FDT\DataLoader\Models;

use App;
use Illuminate\Database\Eloquent\Model;

class LoadingLog extends Model
{
    protected $guarded = [];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function owner()
    {
        return $this->belongsTo(App\User::class, 'user')->withDefault([
            'name' => 'System',
        ]);
    }
}
