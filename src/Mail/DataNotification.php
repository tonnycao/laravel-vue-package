<?php

namespace FDT\DataLoader\Mail;

use FDT\DataLoader\Models\SystemJob;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class DataNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $msg = null;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($msg)
    {
        //
        $this->msg = $msg;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $url = config('app.url');
        $subject = sprintf('%s Data loading service', env('APP_NAME'));
        $job = array_get($this->msg, 'job');
        $mt = array_get($this->msg, 'mt');
        switch ($mt) {
            case SystemJob::MSG_CREATED:
                $subject = 'New data loading request created';
                $mail = 'msg_created';
                $url .= '/loading/request';
                break;
            case SystemJob::MSG_EXCEPTION:
                $subject = 'Discard loaded data request raised';
                $mail = 'msg_exception';
                $url .= '/loading/request/' . $job->id . '#exception';
                break;
            case SystemJob::MSG_LOADED:
                $mail = 'msg_loaded';
                $subject = 'Data loading result';
                $url .= '/loading/request/' . $job->id;
                break;
            case SystemJob::MSG_APPROVED:
                $mail = 'msg_approved';
                $subject = 'Data loading request approved';
                $url .= '/loading/request/' . $job->id;
                break;
            case SystemJob::MSG_REJECTED:
                $mail = 'msg_rejected';
                $subject = 'Data loading request rejected';
                $url .= '/loading/request/' . $job->id;
                break;
            case SystemJob::MSG_EXCEPTION_APPROVED:
                $mail = 'msg_exception_approved';
                $subject = 'Discard loaded data approved';
                $url .= '/loading/request/' . $job->id . '#exception';
                break;
        }

        $viewdata = [
            'job' => $job,
            'mail' => $mail,
            'e' => array_get($this->msg, 'exception'),
            'url' => $url,
        ];

        return $this->subject($subject)
            ->view('dataloader.emails.data.loading', $viewdata);
    }
}
