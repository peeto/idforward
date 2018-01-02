<?php
namespace peeto\idforward;

use peeto\idforward\Codec;

/**
 * idforward class
 *
 * Invokes the core functionality
 *
 */
class idforward extends Codec
{
    public function __construct($config = '')
    {
        parent::__construct($config);
    }

    protected function getTitleHTML($data)
    {
        return '<h1>Link to ' . $data['sitename'] . '</h1>';
    }

    protected function getInputHTML($data)
    {
        $html = '<form method="get" action="' . $data['srcurl'] . '">';
        $html .= '<p>Enter ID or profile URL: <input type="text" name="id" value="' . $data['oid'] . '" />';
        $html .= '<input type="submit" value="Find" /></p>';
        $html .= '</form>';

        return $html;
    }

    protected function getOutputHTML($data)
    {
        $html = '<p>';
        $html .= 'Profile: <a href="' . $data['url'] . '" title="' . $data['id'] . '" target="other">' . $data['id'] . '</a><br />';
        $html .= 'Hexidecimal: <a href="' . $data['hexurl'] . '" title="' . $data['hexid'] . '">' . $data['hexid'] . '</a><br />';
        $html .= $data['qrhtml'] . '<br />';
        //$html .= $data['bchtml'] . '<br />';
        $html .= '</p>';

        return $html;
    }

    public function getHTML($id)
    {
        $result = $this->encode($id);

        $html = $this->getTitleHTML($result);
        $html .= $this->getInputHTML($result);
        if ($result['id']!='') {
            $html .= $this->getOutputHTML($result);
        }

        return $html;
    }
}
