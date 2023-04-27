<?php
/**
 * Récupère les roles de l'utilisateur connecté
 * @return array
 */
function get_current_user_roles() {

    if( is_user_logged_in() ) {

        $user = wp_get_current_user();

        $roles = ( array ) $user->roles;

        return $roles;

    } else {

        return array();

    }

}