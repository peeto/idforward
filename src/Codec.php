<?php
namespace peeto\idforward;

use peeto\idforward\Config;
use Com\Tecnick\Barcode\Barcode;

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
            $barcode = new Barcode();
            $barcodeObj = $barcode->getBarcodeObj(
                'QRCODE',
                $did['url'],
                -8,
                -8,
                'black',
                [0, 0, 0, 0]
            );

            $png = $barcodeObj->getPngData();
            $dataUri = 'data:image/png;base64,' . base64_encode($png);
            $qrhtml = '<img src="data:' . $dataUri . '" />';

            $bcid  = $aid;
            if (strlen($bcid) < 10) $bcid = str_repeat('0', 10 - strlen($bcid));
            $barcode = new Barcode();
            $barcodeObj = $barcode->getBarcodeObj(
                'C128',
                $bcid,
                -2,
                60,
                'black',
                [0, 0, 0, 0]
            );

            $png = $barcodeObj->getPngData();
            $dataUri = 'data:image/png;base64,' . base64_encode($png);
            $bchtml = '<img src="' . $dataUri . '" />';
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
