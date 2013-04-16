<?php

namespace Grid\MultisitePlatform\Authentication;

use Zork\Stdlib\String;
use Zork\Cache\AbstractCacheStorage;

/**
 * AutoLoginToken
 *
 * @author David Pozsar <david.pozsar@megaweb.hu>
 */
class AutoLoginToken extends AbstractCacheStorage
{

    /**
     * @var int
     */
    const TOKEN_LENGTH = 32;

    /**
     * Request an auto-login token
     *
     * @param  string $email
     * @return string token
     */
    public function create( $email )
    {
        $store = $this->getCacheStorage();

        do
        {
            $token = String::generateRandom( self::TOKEN_LENGTH, null, true );
        }
        while ( $store->hasItem( $token ) );

        $store->setItem( $token, $email );
        return $token;
    }

    /**
     * Requested auto-login token's token is valid
     *
     * @param  string $token
     * @return bool
     */
    public function has( $token )
    {
        return $this->getCacheStorage()
                    ->hasItem( $token );
    }

    /**
     * Requested auto-login's email by token
     *
     * @param  string $token
     * @return string email
     */
    public function find( $token )
    {
        return $this->getCacheStorage()
                    ->getItem( $token );
    }

    /**
     * Delete an auto-login token
     *
     * @param  string $token
     * @return bool
     */
    public function delete( $token )
    {
        return $this->getCacheStorage()
                    ->removeItem( $token );
    }

}
