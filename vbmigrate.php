<?php

/*
Plugin Name:  vbmigrate
Version    :  1.0
Description:  Authentication with vBulletin database and password migration.
Author     :  Rand Druid
Author URI :  https://github.com/RandDruid
License    :  MIT
License URI:  https://raw.githubusercontent.com/RandDruid/vbmigrate/main/LICENSE
Text Domain:  vbmigrate
*/


/**
 * Check User Password in VB DB.
 *
 * @param string $password      Password to check against the user
 * @param string $username      User nickname in VB
 * @param \&string $error       Return error from SQL connection
 */
function vb_check_password( $password, $username, &$error ) {
    $return_value = false;
    $error = "";

    // Create connection
    $conn = new mysqli( VB_DB_HOST, VB_DB_USER, VB_DB_PASSWORD, VB_DB_DATABASE );

    // Check connection
    if ($conn->connect_error) {
        $error = $conn->connect_error;
        return $return_value;
    }

    $sql = "SELECT password, salt FROM user WHERE username = '" . $username . "'";

    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        while ( $row = $result->fetch_assoc() ) {
            if ( $row["password"] == md5(md5($password) . $row["salt"]) ) {
                $return_value = true;
                break;
            }
        }
    }
    
    $conn->close();

    return $return_value;
}


/**
 * Check User Password in VB DB if it is not found in WP DB, and save password to WP DB if neccessary.
 *
 * @param WP_User $user         WP_User Object
 * @param string $password      Password to check against the user
 */
function check_vbdb( WP_User $user, $password ) {
 
    if ( ! wp_check_password( $password, $user->user_pass, $user->ID ) ) {
        $error = "";
        if ( vb_check_password( $password, $user->nickname, $error) ) {
            wp_set_password( $password, $user->ID );
            $user = get_user_by( 'id', $user->ID );
        } else {
            $message = esc_html__( 'WP & VB password checks failed. ' . $error, 'text-domain');
            return new WP_Error( 'user_not_verified', $message );
        }
    }
 
    return $user;
}
 
add_filter( 'wp_authenticate_user', 'check_vbdb', 10, 2 );
