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

use Exporter\Exception\InvalidDataFormatException;

class XlsWriter implements WriterInterface
{
    protected $filename;

    protected $file;

    protected $showHeaders;

    protected $position;

    protected $css =
        'table {border-collapse: collapse; width: 100%;}
        .header {border: 1px solid black}
        .date {text-align: left; border-bottom: 1px solid black;}';

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

        if (is_file($filename)) {
            throw new \RuntimeException(sprintf('The file %s already exist', $filename));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function open()
    {
        $this->file = fopen($this->filename, 'w', false);
        fwrite($this->file, "<html><head><meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" /><meta name=ProgId content=Excel.Sheet><meta name=Generator content=\"https://github.com/sonata-project/exporter\">");
    }

    /**
     * {@inheritdoc}
     */
    public function close()
    {
        fwrite($this->file, "</table></body></html>");
        fclose($this->file);
    }

    /**
     * {@inheritdoc}
     */
    public function write(array $data)
    {
        $this->init($data);

        fwrite($this->file, '<tr>');
        foreach ($data as $value) {
            fwrite($this->file, sprintf('<td>%s</td>', $value));
        }
        fwrite($this->file, '</tr>');

        $this->position++;
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
        fwrite($this->file, sprintf('<style type="text/css">%s</style></head><body><table>', $this->css));
        if ($this->showDate) {
            fwrite($this->file, sprintf('<tr><td class="date" colspan="%s">%s</tr>', count($data), $date));
        }
        for ($i = 0; $i < count($this->titles); $i++) {
            fwrite($this->file, sprintf('<tr><th colspan="%s">%s</th></tr>', count($data), str_replace('%date%', $date, $this->titles[$i])));
        }
        if ($this->showHeaders) {
            fwrite($this->file, '<tr>');
            foreach ($data as $header => $value) {
                fwrite($this->file, sprintf('<th class="header">%s</th>', $header));
            }
            fwrite($this->file, '</tr>');
            $this->position++;
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
