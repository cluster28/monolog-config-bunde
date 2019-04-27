<?php

namespace MonologConfigBundle\Importer;

/**
 * Interface ImporterInterface
 * @package MonologConfigBundle\Importer
 */
interface ImporterInterface
{
    /**
     * @return mixed
     */
    public function getConfigs();
}