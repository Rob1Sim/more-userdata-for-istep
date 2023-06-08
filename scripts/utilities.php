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
define("TABLE_MEMBERS_NAME", $wpdb->prefix . 'membre_ISTeP');
/**
 * Nom de la table qui fait la relation entre MEMBRE et TEAM dans la base de donnée
 */
define("TABLE_MEMBERS_TEAM_NAME", $wpdb->prefix . 'membre_equipe_ISTeP');
/**
 * Nom de la table qui enregistre les différents campus
 */
define("TABLE_LOCATION_NAME", $wpdb->prefix . 'localisation_ISTeP');
/**
 * Nom de la table qui enregistre les informations affichées sur la page personnel
 */
define("TABLE_PERSONAL_PAGE_NAME", $wpdb->prefix . 'personal_page_ISTeP');
/**
 * Définie la capacité d'un role à accéder au menu admin du plugin
 */
const ADMIN_CAPACITY = "more_data_users_admin_capacity";
/**
 * Ajout d'un role par défaut sera sélectionné de base dans les roles lors de la création de l'utilisateur
 */


/**
 * Récupère les roles de l'utilisateur connecté
 * @return array
 */
function get_current_user_roles()
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
 * Renvoie le nom de l'équipe correspondant à l'id passé en paramètre
 * @param int|null $id
 * @return mixed|stdClass
 */
function get_team_name_by_id(?int $id)
{
    //Si la class n'éxiste pas on renvoie un objets avec la meme propriété
    if (!isset($id)) {
        $obj = new stdClass();
        $obj->nom_equipe = "Pas d'équipe";

        return $obj;
    }
    global $wpdb;
    $tableName = TABLE_TEAM_NAME;
    return $wpdb->get_results("SELECT nom_equipe FROM $tableName WHERE id_equipe = $id")[0];
}

/**
 * Renvoie toutes les information de l'utilisateur qui possède l'id passé en paramètre
 * @param int $id
 * @param string $type Prend la valeur "wp" ou "istep", désigne si la recherche doit se faire avec l'id wp ou l'id de la table (wp par défaut)
 * @return mixed|stdClass
 */
function get_istep_user_by_id(int $id, string $type ="wp"):mixed
{
    global $wpdb;
    $tableName = TABLE_MEMBERS_NAME;
    if ($type == "wp") {
        return $wpdb->get_results("SELECT * FROM $tableName WHERE wp_user_id = $id")[0];
    }
    if ($type == "istep") {
        return $wpdb->get_results("SELECT * FROM $tableName WHERE id_membre = $id")[0];
    }
    throw new \WPUM\Carbon\Exceptions\InvalidTypeException("Type incorrecte : le paramètre type ne prend que la valeur wp ou istep");
}

/**
 * Récupère tous les élément d'une table donnée
 * @param string $table
 * @return array
 */
function get_list_of_table(string $table):array
{
    global $wpdb;
    $tableName = $table;
    return $wpdb->get_results("SELECT * FROM $tableName");
}


/**
 * Récupère l'avatar de l'utilisateur passé en paramètre
 * @param int $user_id
 * @return string
 */
function get_user_avatar(int $user_id)
{
    $avatar_id = get_user_meta($user_id, 'wp_user_avatar', true);
    if ($avatar_id) {
        if (is_array(wp_get_attachment_image_src($avatar_id, 'thumbnail'))) {
            $avatar_url = wp_get_attachment_image_src($avatar_id, 'thumbnail')[0];
        } else {
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
function convert_tower_into_readable(string $rawName):string
{

    $parts = explode('-', $rawName);

    $tour = ucfirst($parts[0]);
    $floor = str_replace('-', ' ', $parts[1]);
    $level = $parts[2];


    // Afficher le résultat
    return "$tour $floor"."-"." $level"."ème étage";

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

/**
 * Vérifie que l'id passé en paramètre correspond à l'id d'une équipe
 * @param int $id
 * @return bool
 */
function is_team_id_valid(int $id): bool
{
    global $wpdb;
    $table_name = TABLE_TEAM_NAME;
    $teams = $wpdb->get_results("SELECT id_equipe FROM $table_name");
    $array_of_id = [];
    foreach ($teams as $team) {
        $array_of_id[] = $team->id_equipe;
    }

    return in_array($id, $array_of_id);
}

/**
 * Retourne une liste de string contenant le nom de chaque équipe
 * @return array
 */
function get_all_teams_name():array
{
    $list_of_team_table = get_list_of_table(TABLE_TEAM_NAME);
    $list_of_team = [];
    foreach ($list_of_team_table as $team) {
        $list_of_team[]= $team->nom_equipe;
    }
    return $list_of_team;
}

/** Récupérations des données de la table user-teams */

/**
 * Récupère tous les nom de l'utilisateur passé en paramètre
 * @param int $id
 * @return array
 */
function get_user_teams_names_by_user_id(int $id): array
{
    $teams = get_user_teams_by_user_id($id);
    $array_of_name = [];
    foreach ($teams as $team) {
        $array_of_name[] = get_team_name_by_id($team->id_equipe)->nom_equipe;
    }
    return $array_of_name;
}

/**
 * Récupère les équipes d'un utilisateurs
 * @param int $id
 * @return array
 */
function get_user_teams_by_user_id(int $id): array
{
    global $wpdb;
    $table_name = TABLE_MEMBERS_TEAM_NAME;

    $teams = $wpdb->get_results("SELECT id_equipe FROM $table_name WHERE id_membre = $id");
    return $teams;
}

/**
 * Récupère tous les id des équipes de l'utilisateur passé en paramètre
 * @param int $id
 * @return array
 */
function get_user_teams_id_by_user_id(int $id): array
{
    $teams = get_user_teams_by_user_id($id);
    $array_of_id = [];
    foreach ($teams as $team) {
        $array_of_id[] = $team->id_equipe;
    }
    return $array_of_id;
}

/**
 * AJoute une entrée à la table
 * @param array $teams_id_list
 * @param mixed $member_id
 * @return void
 */
function add_data_to_team_members(array $teams_id_list, int $member_id): void
{
    //Si pour une raison quelconque il n'y a pas d'équipe alors on l'attribut à l'équipe "Pas d'équipe"
    global $wpdb;
    if (count($teams_id_list) == 0) {
        $teams_id_list[] = 1;
    }
    //Création d'entités entre les équipes et l'utilisateur
    foreach ($teams_id_list as $team) {
        $wpdb->insert(
            TABLE_MEMBERS_TEAM_NAME,
            array(
                'id_equipe' => intval($team),
                'id_membre' => intval($member_id)
            )
        );
    }
}

/**
 * Supprime une entrée de la table members teams, avec les données passé en paramètres
 * @param int $team_id
 * @param int $member_id
 * @return void
 */
function delete_data_from_team_members(int $team_id, int $member_id): void
{
    //Si pour une raison quelconque il n'y a pas d'équipe alors on l'attribut à l'équipe "Pas d'équipe"
    global $wpdb;

    $wpdb->delete(
        TABLE_MEMBERS_TEAM_NAME,
        array(
            "id_equipe" => $team_id,
            "id_membre" => $member_id
        )
    );
}

/**
 * Retourne le nom d'un campus grâce à son id
 * @param int $id
 * @return string
 */
function get_name_of_location_by_id(int $id):string
{
    global $wpdb;
    $table_name = TABLE_LOCATION_NAME;

    $name = $wpdb->get_results("SELECT nom_localisation FROM $table_name WHERE id_localisation = $id");
    if(count($name)>0) {
        return $name[0]->nom_localisation;
    }
    return "Pas de campus";
}

/**
 * Retourne l'utilisateur WP depuis l'id d'un utilisateur de l'ISTeP
 * @param int $id
 * @return WP_User|false
 */
function get_wp_user_from_istep_user(int $id): WP_User|false
{
    global $wpdb;
    $member_table = TABLE_MEMBERS_NAME;
    $query = $wpdb->get_results("SELECT wp_user_id FROM $member_table WHERE id_membre = $id ");
    if (empty($query)) {
        return false;
    }
    return get_user_by('id', $query[0]->wp_user_id);
}

/**
 * Récupère toutes les infos présente sur la page de l'utilisateur et les renvoie sous la forme d'un tableau
 * @param int $id l'id de l'utilisateur
 * @return array
 */
function get_user_personal_pages_categories(int $id):array
{
    global $wpdb;
    $table = TABLE_PERSONAL_PAGE_NAME;
    $results = $wpdb->get_results("SELECT * FROM $table where wp_user_id = $id");
    $data = array(); // Tableau pour stocker les résultats
    if (!empty($results)) {
        $data = get_object_vars($results[0]);
    }
    return $data;
}

/**
 * Vérifie si le campus entrée existe, si il n'éxiste pas alors l'utilisateur est redirigé (vers une page d'erreur)
 * @param string $campus
 * @param string $current_url
 * @return void
 */
function is_location_existing_redirect_if_not(string $campus, string $redirect_url): void
{
    if (!is_location_existing($campus)) {
        wp_redirect($redirect_url);
        exit();
    }

}

/**
 * Vérifie si le campus existe
 * @param string $campus
 * @return bool
 */
function is_location_existing(string $campus) :bool
{
    global $wpdb;
    $table_name = TABLE_LOCATION_NAME;
    $is_location_existing = "SELECT * FROM $table_name WHERE id_localisation = $campus";
    return !empty($wpdb->get_results($is_location_existing));
}

/**
 * Ajoute ou mets à jour la photo de profile passé dans le formulaire
 * Redirige l'utilisateur vers les sous-liens
 * @param string $file_name nom du fichier $_FILE[]
 * @param string $current_url
 * @param int $user_id
 * @param string $not_allowed_format_link
 * @param string $error_when_uploading_file ce lien devrait posséder un &error-message= pour afficher l'erreur
 * @return bool Retourne vrai si l'image a bien été ajouter ou mis à jour
 */
function add_profile_picture_or_redirect(
    string $file_name,
    string $current_url,
    int $user_id,
    string $not_allowed_format_link,
    string $error_when_uploading_file,
):bool {
    if (isset($_FILES[$file_name]["name"]) && $_FILES[$file_name]["name"]!== "") {

        // Vérifie si le fichier est au format JPG, PNG ou GIF
        $allowed_formats = array('jpg', 'jpeg', 'png', 'gif');
        $extension = strtolower(pathinfo($_FILES['async-upload']['name'], PATHINFO_EXTENSION));

        if(!in_array($extension, $allowed_formats)) {
            wp_redirect($current_url.$not_allowed_format_link);
            exit();

        } else {
            require_once(ABSPATH . 'wp-admin/includes/media.php');
            require_once(ABSPATH . 'wp-admin/includes/file.php');
            require_once(ABSPATH . 'wp-admin/includes/image.php');
            $attachment_id = media_handle_upload('async-upload', 0);
            if(is_wp_error($attachment_id)) {
                wp_redirect($current_url.$error_when_uploading_file. $attachment_id->get_error_message());
                exit();
            } else {
                // Mettez à jour le champ de méta de l'utilisateur avec l'ID de l'attachement
                $user_pp = get_user_meta($user_id, "wp_user_avatar");
                if(empty($user_pp) || $user_pp == "") {
                    add_user_meta($user_id, "wp_user_avatar", $attachment_id);
                } else {
                    update_user_meta($user_id, "wp_user_avatar", $attachment_id);
                }

            }

        }
        return true;

    } else {
        return true;
    }
}
