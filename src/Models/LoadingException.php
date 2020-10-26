<?php

namespace FDT\DataLoader\Models;

use Illuminate\Database\Eloquent\Model;

class LoadingException extends Model
{
    //
    protected $guarded = [];
    const STATUS_NORMAL = 0;
    const STATUS_APPROVED = 1;
    const STATUS_REJECTED = 2;

    /**
     * @param int $status
     *
     * @return mixed
     */
    public static function getText($status)
    {
        $arr = [
            self::STATUS_NORMAL => 'Normal',
            self::STATUS_APPROVED => 'Approved',
            self::STATUS_REJECTED => 'Rejected',
        ];

        return array_get($arr, $status);
    }
}
