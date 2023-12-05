<?php

namespace DTApi\Http\Controllers\Trait;

trait BookingMethodSupportTrait
{
    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ METHOD SUPPORT FOR index

    /**
     * Method isAdminOrSuperAdmin used in index method
     */
    public function isAdminOrSuperAdmin($authenticatedUser): bool
    {
        $userType = $authenticatedUser->user_type;
        $adminRoleId = env('ADMIN_ROLE_ID');
        $superAdminRoleId = env('SUPERADMIN_ROLE_ID');

        return $userType == $adminRoleId || $userType == $superAdminRoleId;
    }
    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ END METHOD SUPPORT FOR index


    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ METHOD SUPPORT FOR distanceFeed
    /**
     * getValueOrDefault To get the value or default value of the data
     */
    public function getValueOrDefault($data, $key)
    {
        return isset($data[$key]) && $data[$key] !== '' ? $data[$key] : '';
    }

    /**
     * getBooleanValue to convert boolean values in string format true or false.
     */
    public function getBooleanValue($data, $key)
    {
        return isset($data[$key]) && $data[$key] == 'true' ? 'yes' : 'no';
    }

    /**
     * getFlaggedValue  to handle 'flagged' related conditions..
     */
    public function getFlaggedValue($data)
    {
        if ($data['flagged'] == 'true') {
            return $data['admincomment'] != '' ? 'yes' : 'Please, add comment';
        }

        return 'no';
    }
    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ END METHOD SUPPORT FOR distanceFeed
}
