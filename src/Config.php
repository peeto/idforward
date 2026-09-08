<?php
namespace peeto\idforward;

/**
 * Config class
 *
 * Loads the configuration for idforward
 */
class Config
{
    protected $config;

    public function __construct(string $configfile)
    {
        if ($configfile!='' && file_exists($configfile)) {
            include $configfile;
        } elseif (file_exists(__DIR__ . '/config/config.php')) {
            include __DIR__ . '/config/config.php';
        } elseif (file_exists((__DIR__ . '/config/config_default.php'))) {
            include __DIR__ . '/config/config_default.php';
        } else {
            throw new \Exception('Barcode configuration missing');
        }
        $this->config = $config;
        unset($config);
    }

    protected function getConfig(string $key)
    {
        return is_array($this->config) && array_key_exists($key, $this->config) ? $this->config[$key] : null;
    }
}
