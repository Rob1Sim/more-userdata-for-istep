<?php
// ------------Fonction diverses utilisé dans le plugin-----------
global $wpdb;
/**
 * Nom de la table équipe dans la base de donnée
 */
define("TABLE_TEAM_NAME", $wpdb->prefix . 'equipe_ISTeP');
/**
 * Nom de la table membre dans la base de donnée
 */
define("TABLE_MEMBERS_NAME",$wpdb->prefix . 'membre_ISTeP');
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

/**
 * Renvoie le nom de l'équipe correspondant à l'id passé en paramètre
 * @param int $id
 * @return mixed|stdClass
 */
function get_team_name_by_id(int $id){
    global $wpdb;
    $tableName = TABLE_TEAM_NAME;
    return $wpdb->get_results("SELECT nom_equipe FROM $tableName WHERE id_equipe = $id")[0];
}

/**
 * Renvoie toutes les information de l'utilisateur qui possède l'id passé en paramètre
 * @param int $id
 * @return mixed|stdClass
 */
function get_istep_user_by_id(int $id):mixed{
    global $wpdb;
    $tableName = TABLE_MEMBERS_NAME;
    return $wpdb->get_results("SELECT * FROM $tableName WHERE wp_user_id = $id")[0];
}

/**
 * Récupère tous les élément d'une table donnée
 * @param string $table
 * @return array
 */
function get_list_of_table(string $table):array{
    global $wpdb;
    $tableName = $table;
    return $wpdb->get_results("SELECT * FROM $tableName");
}


/**
 * Récupère l'avatar de l'utilisateur passé en paramètre
 * @param int $user_id
 * @return string
 */
function get_user_avatar(int $user_id) {
    $avatar_id = get_user_meta($user_id, 'wp_user_avatar', true);
    if ($avatar_id) {
        if (is_array(wp_get_attachment_image_src($avatar_id, 'thumbnail'))){
            $avatar_url = wp_get_attachment_image_src($avatar_id, 'thumbnail')[0];
        }else{
            return "Erreur de chargment de l'image";
        }
    } else {
        $avatar_url = get_avatar_url($user_id);
    }
    return '<img src="' . $avatar_url . '" alt="Avatar">';
}

/**
 * Transforme le nom de la tour enregistré dans la bd
 * @param string $rawName
 * @return string
 */
function convert_tower_into_readable(string $rawName):string{

    $parts = explode('-', $rawName);

    $tour = ucfirst($parts[0]);
    $floor = str_replace('-', ' ', $parts[1]);
    $level = $parts[2];


// Afficher le résultat
    return "$tour $floor"."-"." $level"."ème étage";

}