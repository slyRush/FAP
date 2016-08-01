<?php

/**
 * Class PassHash : to generate password
 */
class PassHash {
    private static $algo = '$2a'; // blowfish
    private static $cost = '$10'; // cost parameter

    /**
     * @return string
     */
    public static function unique_salt() {
        return substr(sha1(mt_rand()), 0, 22);
    }

    /**
     * Hash password
     * @param $password
     * @return string
     */
    public static function hash($password) {
        return crypt($password, self::$algo . self::$cost . '$' . self::unique_salt());
    }

    /**
     * Compare hash password with password entered
     * @param $hash
     * @param $password
     * @return bool
     */
    public static function check_password($hash, $password) {
        $full_salt = substr($hash, 0, 29);
        $new_hash = crypt($password, $full_salt);
        return ($hash == $new_hash);
    }

}
