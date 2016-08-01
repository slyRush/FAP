<?php

/**
 * Class RestrictedUriAccess
 */
class RestrictedUriAccess
{
    /**
     * Get all uris that Superadmin can access
     * @return array
     */
    static function getUriAllowedSuperadmin()
    {
        $URIs = array(
            "/login",
            "/register"
        );
        return $URIs;
    }

    /**
     * Get all uris that Admin can access
     * @return array
     */
    static function getUriAllowedAdmin()
    {
        $URIs = array(
            "/login",
            "/register"
        );
        return $URIs;
    }

    /**
     * Get all uris that Client can access
     * @return array
     */
    static function getUriAllowedClient()
    {
        $URIs = array(
            "/login",
            "/register"
        );
        return $URIs;
    }

    /**
     * Get all uris that Supplier can access
     * @return array
     */
    static function getUriAllowedSupplier()
    {
        $URIs = array(
            "/login",
            "/register"
        );
        return $URIs;
    }

    /**
     * Get all uris that Displayer can access
     * @return array
     */
    static function getUriAllowedAfficheur()
    {
        $URIs = array(
            "/login",
            "/register"
        );
        return $URIs;
    }
}