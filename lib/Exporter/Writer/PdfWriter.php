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
include_once __DIR__.'/../../../../bundles/TFox/Bundle/MpdfPortBundle/mpdf/mpdf.php';

use Exporter\Exception\InvalidDataFormatException;

class PdfWriter implements WriterInterface
{
    protected $filename;

    protected $file;

    protected $showHeaders;

    protected $position;

    protected $html;

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
        $this->html .= "<html><body><table border='1' style='border-collapse: collapse'>";
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
            $this->html .= sprintf('<td>%s</td>', $value);
        }
        $this->html .= '</tr>';

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

        if ($this->showHeaders) {
            $this->html .= '<tr>';
            foreach ($data as $header => $value) {
                $this->html .= sprintf('<th>%s</th>', $header);
            }
            $this->html .= '</tr>';
            $this->position++;
        }
    }
}
