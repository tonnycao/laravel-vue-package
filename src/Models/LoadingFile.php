<?php

namespace FDT\DataLoader\Models;

use Illuminate\Database\Eloquent\Model;

class LoadingFile extends Model
{
    protected $guarded = [];
    //
    const STATUS_NORMAL = 0;
    const STATUS_INACTIVATING = 1;
    const STATUS_INACTIVATED = 2;

    /**
     * @param int $status
     *
     * @return mixed
     */
    public static function text($status)
    {
        $text = [
            self::STATUS_NORMAL => 'normal',
            self::STATUS_INACTIVATING => 'inactivating',
            self::STATUS_INACTIVATED => 'inactivated',
        ];
        return array_get($text, $status);
    }
}
