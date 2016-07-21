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
    public function generateIdentifier();

    /**
     * Sets an identifier
     *
     * @param string $identifier
     * @return mixed
     */
    public function setIdentifier($identifier);

    /**
     * Returns current identifier
     *
     * @return string
     */
    public function getIdentifier();
}
