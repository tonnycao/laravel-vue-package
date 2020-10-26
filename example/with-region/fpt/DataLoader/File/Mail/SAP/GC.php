<?php

namespace FDT\DataLoader\File\Mail;

use FDT\DataLoader\Repositories\File\Mail\MailDownloader;

class GC extends MailDownloader
{
    protected $sap_conf = null;

    public function __construct($job)
    {
        //get configuration file
        $conf = config('dataloader.mailbox');
        $this->sap_conf = $sap_conf = config('dataloader.SAP');
        $this->job = $job;
        if (config('datasource.by_region')) {
            $this->setRegion('GC');
        }
        $this->setUp($job->type, $job->subtype, $sap_conf, $conf);
    }

    /**
     * @return string
     */
    function prepareSearch()
    {
        $from_addresses = explode(';', array_get($this->sap_conf, 'from'));
        $array = [];
        for ($i = 0; $i < count($from_addresses); $i++) {
            $array[$i] = " ";
        }
        $text = join("OR ", $array);
        foreach ($from_addresses as $from_address) {
            $text .= sprintf('SUBJECT "%s" FROM "%s" ', array_get($this->sap_conf, 'subject'),
                $from_address);
        }

        return trim($text);
    }

    function beforeFetching($info)
    {
        // TODO: Implement beforeFetching() method.
        return true;
    }

    /**
     * @param $parts
     * @param $msg_num
     * @param string $level
     */
    protected function _read_part($parts, $msg_num, $level = "0")
    {
        foreach ($parts as $partno => $partarr) {
            if (str_contains($level, ".")) {
                $next_level = $level . ($partno + 1);
            } else {
                $next_level = $partno + 1;
            }
            if (!empty($partarr->parts)) {
                $this->_read_part($partarr->parts, $msg_num, $next_level . '.');
            } elseif ($partarr->ifdparameters > 0) {
                foreach ($partarr->dparameters as $dparam) {
                    \Log::info($this->file_pattern);
                    if ((strtoupper($dparam->attribute) == 'FILENAME') && stripos(strtolower($dparam->value),
                            $this->file_pattern) !== false
                    ) {
                        $tmp_file = storage_path('app/' . $this->path . '/' . $dparam->value . '.tmp');
                        $tmp = fopen($tmp_file, 'w');
                        $file = storage_path('app/' . $this->path . '/' . $dparam->value);
                        $fp = fopen($file, 'w');
                        $filter = null;
                        if ($partarr->encoding == 0 || $partarr->encoding == 1 || $partarr->encoding == 2) {
                            imap_savebody($this->mbox, $tmp, $msg_num, $next_level);
                        } elseif ($partarr->encoding == 3) {
                            $filter = stream_filter_append($tmp, 'convert.base64-decode', STREAM_FILTER_WRITE);
                            imap_savebody($this->mbox, $tmp, $msg_num, $next_level);
                        } elseif ($partarr->encoding == 4) {
                            imap_savebody($this->mbox, $tmp, $msg_num, $next_level);
                        }

                        if ($filter) {
                            stream_filter_remove($filter);
                        }
                        fclose($tmp);
                        $tmp = fopen($tmp_file, 'r');
                        //$filter = stream_filter_append($tmp, 'convert.quoted-printable-decode', STREAM_FILTER_READ);
                        while ($line = fgets($tmp)) {
                            $line = quoted_printable_decode($line);
                            $line = preg_replace("/\r+/", "\r", $line);
                            if (ends_with($line, "\r")) {
                                $line = preg_replace("/\r+/", "", $line);
                            }
                            fwrite($fp, $line);
                        }
                        fclose($tmp);
                        fclose($fp);
                        unlink($tmp_file);
                        imap_mail_move($this->mbox, $msg_num, $this->move_to);
                    }
                }
            }
        }
    }
}
