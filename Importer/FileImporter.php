<?php

namespace MonologConfigBundle\Importer;

use Symfony\Component\Yaml\Yaml;

/**
 * Class FileImporter
 * @package MonologConfigBundle\Importer
 */
class FileImporter implements ImporterInterface
{
    /**
     * @var array
     */
    protected $arrayFiles;

    /**
     * FileImporter constructor.
     * @param array $arrayFiles
     */
    public function __construct($arrayFiles = [])
    {
        $this->arrayFiles = $arrayFiles;
    }

    /**
     * @return array
     */
    public function getConfigs()
    {
        $arrayConfigs = [];
        foreach ($this->arrayFiles as $file) {
            if (is_file($file)) {
                $arrayConfigs = array_merge($arrayConfigs, Yaml::parseFile($file));
            }
        }
        return $arrayConfigs;
    }
}