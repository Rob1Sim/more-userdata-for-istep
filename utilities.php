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

/**
 * Vérifie si l'utilisateur connecté possède les rôles passé en paramètres,
 * (les roles doivent écrite selon leur slug,=> en minuscule est sans caractères spéciaux : espace, accent)
 * A noté que si l'utilisateur n'est pas connecté la fonction renvoie false
 * @param array $roles
 * @return bool
 */
function can_user_create_users(array $roles): bool{
    $currentUsersRoles = get_current_user_roles();
    $isGranted = false;
    if (get_current_user_roles()>0){
        foreach ($roles as $role){
            if (in_array($role,$currentUsersRoles)){
                $isGranted = true;
            }
        }
    }
    return $isGranted;
}