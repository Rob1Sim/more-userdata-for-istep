<?php
/*
Plugin Name: More userData for ISTeP
Plugin URI: https://github.com/Rob1Sim/more-userdata-for-istep
Description: Ajoute un formulaire de création d'utilisateur pensé pour l'ISTeP et une page personalisé pour les utilsateurs, ainsi qu'une gestions des équipes.
!Nécéssite un plugin qui gère les permissions pour les roles!
Plus d'informations dans le fichier README.md
Author: Robin Simonneau
Version: 1.0
Author URI: https://robin-sim.fr/
*/
wp_enqueue_style('more-userdata-for-istep', plugins_url('styles/more-userdata-for-istep.css', __FILE__));
wp_enqueue_script('more-userdata-for-istep-js', plugins_url('scripts/more-userdata-for-istep.js', __FILE__), array(), false, true);

require_once(plugin_dir_path(__FILE__).'src/utilities.php');
require_once(plugin_dir_path(__FILE__) . 'src/admin-functions.php');
require_once(plugin_dir_path(__FILE__) . 'src/tiny_directory.php');
require_once(plugin_dir_path(__FILE__) . 'src/personal_pages.php');
require_once(plugin_dir_path(__FILE__) . 'src/add_user_form.php');

/**
 * Créer la base de donnée lors de l'activation du plugin
 * @return void
 */
function more_ud_istep_install(): void
{
    global $wpdb;
    $table_name_user_data = TABLE_MEMBERS_NAME;
    $table_name_user_team = TABLE_TEAM_NAME;
    $table_members_team = TABLE_MEMBERS_TEAM_NAME;
    $table_name_user_location = TABLE_LOCATION_NAME;
    $table_personal_page = TABLE_PERSONAL_PAGE_NAME;
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "
        CREATE TABLE $table_name_user_team(
            id_equipe INT NOT NULL AUTO_INCREMENT,
            nom_equipe VARCHAR(255) NOT NULL,
            PRIMARY KEY(id_equipe)
        )$charset_collate;
        
        CREATE TABLE $table_members_team(
            id_equipe INT NOT NULL ,
            id_membre INT NOT NULL,
            PRIMARY KEY(id_equipe,id_membre),
            FOREIGN KEY (id_equipe) REFERENCES {$wpdb->prefix}equipe_ISTeP(id_equipe) ON DELETE CASCADE,
            FOREIGN KEY (id_membre) REFERENCES {$wpdb->prefix}membre_ISTeP(id_membre) ON DELETE CASCADE
    
        )$charset_collate;
        
        CREATE TABLE $table_name_user_location(
            id_localisation INT NOT NULL AUTO_INCREMENT,
            nom_localisation VARCHAR(255) NOT NULL,
            PRIMARY KEY(id_localisation)
        )$charset_collate;
        
        CREATE TABLE $table_name_user_data (
            id_membre INT NOT NULL AUTO_INCREMENT,
            wp_user_id BIGINT UNSIGNED NOT NULL,
            fonction VARCHAR(255),
            nTelephone VARCHAR(10),
            bureau VARCHAR(4),
            rangEquipe VARCHAR(255),
            tourDuBureau VARCHAR(30),
            campus_location VARCHAR(255),
            employeur VARCHAR(255),
            caseCourrier VARCHAR(10),
            PRIMARY KEY (id_membre),
            FOREIGN KEY (wp_user_id) REFERENCES {$wpdb->prefix}users(ID)
                ON DELETE CASCADE,
            FOREIGN KEY(campus_location)  REFERENCES {$wpdb->prefix}localisation_ISTeP(id_localisation)
                           ON DELETE CASCADE,
    ) $charset_collate;
        CREATE TABLE $table_personal_page(
            id_page INT NOT NULL AUTO_INCREMENT,
            wp_user_id BIGINT UNSIGNED NOT NULL,
            enseignement LONGTEXT,
            responsabilite LONGTEXT,
            projets LONGTEXT,
            parcours LONGTEXT,
            activite_technique LONGTEXT,
            bibliographie LONGTEXT,
            divers LONGTEXT,
            PRIMARY KEY(id_page),
            FOREIGN KEY (wp_user_id) REFERENCES {$wpdb->prefix}users(ID)
            ON DELETE CASCADE
            )$charset_collate;

";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);

    $page_data = array(
        'post_title' => "Membres de l'ISTeP",
        'post_content' => '[users_directory]',
        'post_status' => 'publish',
        'post_type' => 'page',
        'post_name' => 'membres-istep'

    );

    // Insère la page dans la base de données de WordPress
    wp_insert_post($page_data);

    //On ajoute les roles de bases pour éditer le plugin
    update_option('admin_user_roles', ["administrator"]);
    update_option('istep_user_roles', ["administrator"]);
    $role_obj = get_role("administrator");
    $role_obj->add_cap(ADMIN_CAPACITY);
    $wpdb->insert(
        TABLE_TEAM_NAME,
        array(
            'id_equipe' => 1,
            'nom_equipe' => "Pas d'équipe"
        )
    );
    $wpdb->insert(
        TABLE_LOCATION_NAME,
        array(
            'id_localisation' => 1,
            'nom_localisation' => "Sorbonne Université - Campus Pierre et Marie Curie"
        )
    );

    //Role par défaut
    update_option('default_role', "subscriber");
    //Lien de redirection par défaut
    update_option('default_redirect_link', "sample-page");

    create_modify_personal_page();
}
register_activation_hook(__FILE__, 'more_ud_istep_install'); //Appelé lors de l'activation du plugin


/**
 * Est lancé lorsque le plugin est désactivé
 * @return void
 */
function on_deactivating(): void
{
    $page_personal = get_page_by_path('modifier-votre-page-personnel');
    $page_personal_id = $page_personal->ID;
    wp_delete_post($page_personal_id, true); // Déplace la page vers la corbeille

    $page_members = get_page_by_path('membres-istep');
    $page_members_id = $page_members->ID;
    wp_delete_post($page_members_id, true); // Déplace la page vers la corbeille

}
register_deactivation_hook(__FILE__, 'on_deactivating');
