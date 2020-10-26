<?php

namespace FDT\DataLoader\SAP;

use FDT\DataLoader\Models\SystemJob as Job;
use FDT\DataLoader\Repositories\LoaderInterface;

abstract class Loader implements LoaderInterface
{
    public $str = '';
    public $job = null;

    /**
     * @return array
     */
    public static function logs()
    {
        $content = [
            'ok' => true,
            'fiscal_quarter_year' => '',
            'fiscal_month_year' => '',
            'Total Lines' => 0,
            'Parsed Lines' => 0,
            'units' => 0,
            'lc' => 0,
            'usd' => 0,
            'week' => [],
        ];
        return $content;
    }

    /**
     * @param bool $logs
     * @param string $prefix
     * @return string
     */
    public function format($logs = false, $prefix = '')
    {
        if (is_array($logs)) {
            foreach ($logs as $k => $v) {
                if (is_array($v)) {
                    ksort($v);
                    $this->format($v, $k);
                } else {
                    $this->str .= $k . ':' . $v . "\n";
                }
            }

            return $this->str;
        } else {
            if ($logs === true) {
                return $this->str;
            } elseif ($logs) {
                $this->str .= "=======================================\n";
                $this->str .= $logs;
                $this->str .= "\n=======================================\n";
            } else {
                $this->str .= "=======================================\n";
            }
        }
    }

    protected function persist()
    {
        //reset all the logs string.
        if ($this->job && $this->job instanceof Job) {
            $this->job->summary = $this->str;
            $this->job->save();
        }
        $this->str = '';
    }
}
