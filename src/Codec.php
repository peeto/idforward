<?php
namespace peeto\idforward;

use peeto\idforward\Config;
use CodeItNow\BarcodeBundle\Utils\QrCode;
use CodeItNow\BarcodeBundle\Utils\BarcodeGenerator;

/**
 * Codec class
 *
 * Abstracts encoding/decoding functionality from the rest of the code *
 */
class Codec extends Config
{
    public function __construct($config)
    {
        parent::__construct($config);
    }

    /**
     * function decode
     */
    protected function decode($id)
    {
        $desturl = $this->getConfig('DEST_SITE_IDURL');
        $srcurl = $this->getConfig('SRC_SITE_IDURL');
        $did = '';

        if (substr($id, 0, 1)=='x') {
            $id = hexdec(substr($id, 1));
        }

        if (is_numeric($id)) {
            // ID
            $did = intval($id);
        } elseif (substr(strtolower($id), 0, strlen($desturl)) === strtolower($desturl)) {
            // Destination URL
            $did = intval(substr($id, strlen($desturl)));
        } elseif (substr(strtolower($id), 0, strlen($srcurl)) === strtolower($srcurl)) {
            // Source (this site) URL
            $did = intval(substr($id, strlen($srcurl)));
        }

        $hexid = 'x' . strtoupper(dechex($did));

        return [
            'id' => $did,
            'url' => $desturl . $did,
            'hexid' => $hexid,
            'hexurl' => $srcurl . $hexid
        ];
    }

    /**
     * function encode
     *
     * @todo move barcode configuration to configuration
     *
     */
    protected function encode($id)
    {
        $did = $this->decode($id);
        $aid = $did['id'];
        $qrhtml = '';
        $bchtml = '';

        if ($aid!='') {
            $qrCode = new QrCode();
            $qrCode->setText($did['url']);
            $qrCode->setSize(300);
            $qrCode->setPadding(10);
            $qrCode->setErrorCorrection('high');
            $qrCode->setForegroundColor(array('r' => 0, 'g' => 0, 'b' => 0, 'a' => 0));
            $qrCode->setBackgroundColor(array('r' => 255, 'g' => 255, 'b' => 255, 'a' => 0));
            $qrCode->setLabel($this->getConfig('DEST_SITE_NAME'));
            $qrCode->setLabelFontSize(16);
            $qrCode->setImageType(QrCode::IMAGE_TYPE_PNG);
            $qrhtml = '<img src="data:'.$qrCode->getContentType().';base64,'.$qrCode->generate().'" />';

            $barcode = new BarcodeGenerator();
            $barcode->setText($aid . ' ' . $this->getConfig('DEST_SITE_NAME'));
            $barcode->setType(BarcodeGenerator::Code128);
            $barcode->setScale(2);
            $barcode->setThickness(25);
            $barcode->setFontSize(10);
            $bchtml = '<img src="data:image/png;base64,'.$barcode->generate().'" />';
        }

        $result = [
            'oid' => $id,
            'sitename' => $this->getConfig('DEST_SITE_NAME'),
            'siteurl' => $this->getConfig('DEST_SITE_URL'),
            'srcurl' => $this->getConfig('SRC_SITE_IDURL'),
            'qrhtml' => $qrhtml,
            'bchtml' => $bchtml
        ];

        return $did + $result;
    }
}
