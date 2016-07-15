<?php

namespace Frigg\KeeprBundle\Sanitize;


/**
 * Interface SanitizableIdentifierInterface
 */
interface SanitizableIdentifierInterface
{
    /**
     * Generate a safe identifier
     *
     * @return string
     */
    public function generateSanitizedIdentifier();
}
