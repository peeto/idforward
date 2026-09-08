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
    public function __construct(string $config)
    {
        parent::__construct($config);
    }

    /**
     * function translateSize
     *
     * Translates a size string into a number for the barcode library
     */
    protected function translateSize(?string $size): ?int
    {
        if ($size === null) {
            return null;
        }

        if (substr($size, -1) == '%') {
            $size = intval(substr($size, 0, -1));
            $size = 0 - round($size / 100);
        } else {
            $size = intval($size);
        }

        return $size != 0 ? $size : null;

    }

    /**
     * function decode
     */
    protected function decode(string $id)
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
     */
    protected function encode(string $id): array
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
                $this->translateSize($this->getConfig('QR_WIDTH')) ?? -8,
                $this->translateSize($this->getConfig('QR_HEIGHT')) ?? -8,
                $this->getConfig('QR_COLOR') ?? 'black',
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
                $this->translateSize($this->getConfig('BC_WIDTH')) ?? -2,
                $this->translateSize($this->getConfig('BC_HEIGHT')) ?? 60,
                $this->getConfig('BC_COLOR') ?? 'black',
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
