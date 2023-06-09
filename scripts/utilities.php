<?php

// ------------Fonction diverses utilisé dans le plugin-----------
global $wpdb;

/**
 * Définie la capacité d'un role à accéder au menu admin du plugin
 */
const ADMIN_CAPACITY = "more_data_users_admin_capacity";


/**
 * Récupère les roles de l'utilisateur connecté
 * @return array
 */
function get_current_user_roles(): array
{

    if(is_user_logged_in()) {
        $user = wp_get_current_user();
        $roles = ( array ) $user->roles;
        return $roles;
    } else {
        return array();
    }
}

/**
 * Vérifie si l'utilisateur connecté possède un des rôles passé en paramètres,
 * (les roles doivent écrite selon leur slug,=> en minuscule est sans caractères spéciaux : espace, accent)
 * A noté que si l'utilisateur n'est pas connecté la fonction renvoie false
 * @param array $roles
 * @return bool
 */
function can_user_access_this(array $roles): bool
{
    $currentUsersRoles = get_current_user_roles();
    $isGranted = false;
    if (get_current_user_roles()>0) {
        foreach ($roles as $role) {
            if (in_array($role, $currentUsersRoles)) {
                $isGranted = true;
            }
        }
    }
    return $isGranted;
}


/**
 * Supprime la capacitée d'un role si celui-ci ne fait pas partie de la liste des personne qui devrait l'avoir, retourne la liste mis à jour
 * @param string $cap
 * @param array $listOfRoleWithTheCap
 * @return array
 */
function delete_cap_if_no_need_anymore(string $cap, array $listOfRoleWithTheCap):array
{
    $roles = wp_roles()->roles;
    foreach ($roles as $role_name => $role_details) {
        $role = get_role($role_name);
        if ($role->has_cap($cap)&& !in_array($role->name, $listOfRoleWithTheCap)) {
            $role->remove_cap($cap);
            $key = array_search($role->name, $listOfRoleWithTheCap);
            if ($key !== false) {
                unset($listOfRoleWithTheCap[$key]);
            }
        }
    }
    return $listOfRoleWithTheCap;
}






