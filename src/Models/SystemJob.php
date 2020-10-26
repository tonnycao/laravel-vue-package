<?php

namespace FDT\DataLoader\Models;

use App;
use Illuminate\Database\Eloquent\Model;
use Mail;

class SystemJob extends Model
{
    protected $guarded = [];
    //
    const FORMAT_DATE = 'Y-m-d';

    const STATUS_INITIALIZED = 0;
    const STATUS_APPROVED = 1;
    const STATUS_REJECTED = 2;
    const STATUS_LOADING = 3;
    const STATUS_FINISHED = 4;
    const STATUS_REMOVED = 5;
    const STATUS_FAILED = 6;

    //MSG TYPE
    const  MSG_CREATED = 1; //Loading request created , require approval
    const  MSG_EXCEPTION = 2; //Exception request created, require approval
    const  MSG_APPROVED = 10; //Loading request approved
    const  MSG_REJECTED = 11; //loading request rejected
    const  MSG_EXCEPTION_APPROVED = 12; //Exception approved
    const  MSG_EXCEPTION_REJECTED = 13; //exception rejected
    const  MSG_LOADED = 14; // loading request result.

    const TEXT = [
        self::STATUS_INITIALIZED => 'Initialized',
        self::STATUS_APPROVED => 'Approved',
        self::STATUS_REJECTED => 'Rejected',
        self::STATUS_LOADING => 'Loading',
        self::STATUS_FINISHED => 'Finished',
        self::STATUS_REMOVED => 'Removed',
        self::STATUS_FAILED => 'Failed',
    ];

    const DOWNLOAD_EMAIL_NO = 0;
    const DOWNLOAD_EMAIL_YES = 1;

    const TEXT_DOWNLOAD = [
        self::DOWNLOAD_EMAIL_NO => 'No',
        self::DOWNLOAD_EMAIL_YES => 'Yes',
    ];

    const SOURCE_MAIL = 'mail';
    const SOURCE_SFTP = 'sftp';
    const SOURCE_BOX = 'box';

    const TEXT_SOURCE = [
        self::SOURCE_MAIL => 'Mail',
        self::SOURCE_SFTP => 'SFTP',
        self::SOURCE_BOX => 'Box',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function owner()
    {
        return $this->belongsTo(App\User::class, 'user')->withDefault(
            [
                'name' => 'System',
            ]
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function config()
    {
        return $this->belongsTo(SystemSchedule::class, 'system_config_id')->withDefault(
            [
                'timezone' => config('app.timezone'),
            ]
        );
    }

    /**
     * @param int $mt
     * @param bool $exception
     */
    public function message($mt, $exception = false)
    {
        $emails = $this->getReceipt($mt, $exception);

        if (stripos($emails, ';') !== false) {
            $mails = explode(';', $emails);
        } else {
            $mails = [$emails];
        }

        foreach ($mails as $mail) {
            if (empty($mail)) {
                continue;
            }
            $message = [
                'job' => $this,
                'mt' => $mt,
                'exception' => $exception,
            ];
            Mail::to($mail)->queue(new \FDT\DataLoader\Mail\DataNotification($message));
        }
    }

    /**
     * @param array $systemJobConfigIdsByType
     * @param string $time
     * @param string|null $connection
     *
     * @return mixed
     */
    public static function hasSystemJob(array $systemJobConfigIdsByType, $time, $connection = null)
    {
        return self::on($connection)->whereIn('system_config_id', $systemJobConfigIdsByType)
            ->whereNull('auto_cancelled_at')
            ->where('scheduled_at', '=', $time)
            ->where(function ($q) {
                $q->where('status', '=', self::STATUS_APPROVED)
                    ->orWhere('status', '=', self::STATUS_FINISHED);
            })
            ->get()
            ->all();
    }

    /**
     * @param int $mt
     * @param bool $exception
     *
     * @return string
     */
    protected function getReceipt($mt, $exception = false)
    {
        if ($exception) {
            $user = $exception->user;
        } else {
            $user = $this->user;
        }
        $mail = App\User::find($user)->email;

        if (in_array($mt, [SystemJob::MSG_EXCEPTION,])) {
            $mail .= ';' . env('FDT_TEAM_EMAIL');
        }

        return $mail;
    }
}
