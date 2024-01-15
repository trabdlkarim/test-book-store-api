<?php

namespace App\Http\Resources;

use App\Models\User;
use Exception;
use Luracast\Restler\iProvideMultiVersionApi;
use Luracast\Restler\RestException;

/**
 * User Resource
 * 
 * @access protected
 */
class UserResource implements iProvideMultiVersionApi
{
    /**
     * Get all the users
     * 
     * @return array {@type User}
     */
    public function index()
    {
        return User::all();
    }

    /**
     * Get user details
     * 
     * Get a specific user details
     * 
     * @param int $id ID of the user to get {@from path}
     * 
     * @return object {@type User}
     */
    public function get($id)
    {
        try {
            return User::findOrFail($id);
        } catch (Exception $ex) {
            throw new RestException(404, 'No user found with id = ' . $id);
        }
    }

    public static function __getMaximumSupportedVersion()
    {
        return 2;
    }
}
