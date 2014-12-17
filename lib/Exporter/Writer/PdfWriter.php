<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Exporter\Writer;

//TODO library for mPDF
define("_MPDF_TEMP_PATH", __DIR__.'/../../../../../web/files/tmp/');
include_once __DIR__.'/../../../../bundles/TFox/Bundle/MpdfPortBundle/mpdf/mpdf.php';

use Exporter\Exception\InvalidDataFormatException;

class PdfWriter implements WriterInterface
{
    protected $filename;

    protected $file;

    protected $showHeaders;

    protected $position;

    protected $html;

    protected $css =
        'table {border-collapse: collapse; width: 100%;}
        .title {border-top: 0; border-left: 0; border-right: 0;}
        .date {border-bottom: 1px solid black;}';

    protected $showDate = false;

    protected $titles = [];
    /**
     * @throws \RuntimeException
     *
     * @param      $filename
     * @param bool $showHeaders
     */
    public function __construct($filename, $showHeaders = true)
    {
        $this->filename    = $filename;
        $this->showHeaders = $showHeaders;
        $this->position    = 0;
        if (!is_dir(_MPDF_TEMP_PATH)) {
            mkdir(_MPDF_TEMP_PATH);
        }
        if (is_file($filename)) {
            throw new \RuntimeException(sprintf('The file %s already exist', $filename));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function open()
    {
        $this->html .= "<html><head>";
    }

    /**
     * {@inheritdoc}
     */
    public function close()
    {
        $this->html .= "</table></body></html>";

        $mpdf = new \mPDF('utf-8');

        $mpdf->WriteHTML($this->html);

        $mpdf->Output($this->filename,'f');
    }

    /**
     * {@inheritdoc}
     */
    public function write(array $data)
    {
        $this->init($data);

        $this->html .= '<tr>';
        foreach ($data as $value) {
            $this->html .= sprintf('<td align="center">%s</td>', $value);
        }
        $this->html .= '</tr>';

        $this->position++;
    }

    protected $title;
    protected $branch;
    protected $period;

    /**
     * @param mixed $branch
     */
    public function setBranch($branch)
    {
        $this->branch = $branch;
    }

    /**
     * @return mixed
     */
    public function getBranch()
    {
        return $this->branch;
    }

    /**
     * @param mixed $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @return mixed
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param mixed $period
     */
    public function setPeriod($period)
    {
        $this->period = $period;
    }

    /**
     * @return mixed
     */
    public function getPeriod()
    {
        return $this->period;
    }

    /**
     * @param $data
     *
     * @return array mixed
     */
    protected function init($data)
    {
        if ($this->position > 0) {
            return;
        }
        $date = date('d.m.Y H:i:s');
        $this->html .= sprintf('<style type="text/css">%s</style></head><body>', $this->css);
//        if ($this->showDate) {
//            $this->html .= sprintf('<p align="left"><small>%s</small><table border="1">', $date);
//        }
        if($this->title) {
            $this->html .= sprintf('<p align="left">%s</p></br>', $this->title);
        }
        if($this->branch) {
            $this->html .= sprintf('<p align="left">%s</p></br>', $this->branch);
        }
        if($this->period) {
            $this->html .= sprintf('<p align="left">%s</p></br>', $this->period);
        }
        if ($this->showDate) {
            $this->html .= sprintf('<p align="left">Дата выгрузки: %s</p><table border="1">', $date);
        }

        for ($i = 0; $i < count($this->titles); $i++) {
            $this->html .= sprintf('<tr><th colspan="%s" class="title">%s</th></tr>', count($data), str_replace('%date%', $date, $this->titles[$i]));
        }
        if ($this->showHeaders) {
            $this->html .= '<tr>';
            foreach ($data as $header => $value) {
                $this->html .= sprintf('<th>%s</th>', $header);
            }
            $this->html .= '</tr>';
            $this->position++;
        }
        if (!count($data)) {
            $this->html .= '<tr><td>Отсутствуют исходные данные!</tr>';
        }
    }

    public function setShowDate($showDate)
    {
        $this->showDate = $showDate;
    }

    public function getShowDate()
    {
        return $this->showDate;
    }

    public function setTitles($titles)
    {
        $this->titles = $titles;
    }

    public function getTitles()
    {
        return $this->titles;
    }
}
