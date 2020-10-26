<?php

namespace FDT\DataLoader\Repositories\File\Mail;

use FDT\DataLoader\Models\SystemJob;
use FDT\DataLoader\Repositories\File\Downloader;
use Storage;

abstract class MailDownloader implements Downloader
{
    protected $mbox = null;
    protected $file_pattern = null;
    protected $path = null;
    protected $move_to = null;
    protected $region = null;
    /** @var SystemJob|null */
    public $job = null;

    abstract function prepareSearch();

    abstract function beforeFetching($info);

    /**
     * @param string $region
     */
    public function setRegion($region)
    {
        $this->region = $region;
    }

    /**
     * @return string|null
     */
    public function getRegion()
    {
        return $this->region;
    }

    /**
     * @param string $mbox
     */
    public function setMbox($mbox)
    {
        $this->mbox = $mbox;
    }

    /**
     * @param string $file_pattern
     */
    public function setFilePattern($file_pattern)
    {
        $this->file_pattern = $file_pattern;
    }

    /**
     * @param string $path
     */
    public function setPath($path)
    {
        $this->path = $path;
    }

    /**
     * @param string $move_to
     */
    public function setMoveTo($move_to)
    {
        $this->move_to = $move_to;
    }

    /**
     * @param string $host
     * @param string $user
     * @param string $pass
     *
     * @return bool
     */
    protected function connect($host, $user, $pass)
    {
        $mbox = imap_open($host, $user, $pass);
        $ok = false;
        if (imap_check($mbox)) {
            $this->setMbox($mbox);
            $ok = true;
        }
        return $ok;
    }

    /**
     *
     */
    protected function beforeDownload()
    {
        $ready = $this->mbox && $this->file_pattern && $this->path && $this->move_to;
        if (!$ready) {
            throw new \RuntimeException("Missing necessary information for downloading file from MAILBOX");
        }
    }

    public function download()
    {
        $this->beforeDownload();
        if ($this->job->download) {
            $results = [];
            foreach ($this->prepareSearch() as $criteria) {
                $matched = imap_search($this->mbox, $criteria);
                if (!$matched) {
                    $matched = [];
                }
                $results = array_merge($results, $matched);
            }
            foreach ($results as $msg_num) {
                $this->_fetch_attachment($msg_num);
            }
        }
        @imap_expunge($this->mbox);
        @imap_close($this->mbox, CL_EXPUNGE);
        return $this->path;
    }

    /**
     * @param $msg_num
     * @return bool
     */
    protected function _fetch_attachment($msg_num)
    {
        //dd(imap_headerinfo($this->mbox,$msg_num));
        if (!$this->beforeFetching(imap_headerinfo($this->mbox, $msg_num))) {
            return true;
        }
        $msg_structure = imap_fetchstructure($this->mbox, $msg_num);
        if (count($msg_structure->parts) > 0) {
            $this->_read_part($msg_structure->parts, $msg_num);
        }
    }

    /**
     * @param array $parts
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
                    if ((strtoupper($dparam->attribute) == 'FILENAME') && starts_with(strtolower($dparam->value),
                            $this->file_pattern)) {
                        //$this_part= imap_fetchbody($this->mbox,$msg_num,$next_level);
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

    /**
     * @param string $path
     */
    protected function enablePathExist($path)
    {
        $check_path = 'app/' . $path;
        if (!file_exists(storage_path($check_path))) {
            Storage::makeDirectory($path);
        }
    }

    /**
     * @param string $type
     * @param string $subtype
     * @param array $region_conf
     * @param array $conf
     */
    protected function setUp($type, $subtype, array $region_conf, array $conf): void
    {
        //connect to mail server
        $this->connect(str_replace('INBOX', array_get($region_conf, 'inbox'), array_get($conf, 'host')),
            array_get($conf, 'user'), array_get($conf, 'pass'));
        //set attached file pattern
        $pattern = !empty($this->job->pattern) ? $this->job->pattern : array_get($region_conf, $subtype);
        $this->setFilePattern($pattern);
        if (config('dataloader.by_region')) {
            $path = sprintf(config('dataloader.path'), $type, $this->getRegion());
        } else {
            $path = sprintf(config('dataloader.path'), $type);
        }
        $report = $path . '/' . config('dataloader.report') . '/' . $this->job->id;
        $history = $path . '/' . config('dataloader.history');
        $this->enablePathExist($report); //make sure the path existing
        $this->enablePathExist($history);
        //local path
        $this->setPath($report);
        //after processing which folder to move the message
        $this->setMoveTo(array_get($region_conf, 'move_to'));
    }
}
