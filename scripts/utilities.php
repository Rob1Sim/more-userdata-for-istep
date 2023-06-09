<?php

// ------------Fonction diverses utilisé dans le plugin-----------
global $wpdb;

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



/** Récupérations des données de la table user-teams */



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
