<?php
/*
Plugin Name: More userData for ISTeP
Plugin URI: https://wpusermanager.com/
Description: Ajoute de nouveaux champs à renseigner lors de la création d'un utilisateur pour les membres de l'ISTeP
Author: Robin Simonneau, Arbër Jonuzi
Version: 1.0
Author URI: https://robin-sim.fr/
*/
require_once(plugin_dir_path(__FILE__).'utilities.php');

/**
 * Créer la base de donnée lors de l'activation du plugin
 * @return void
 */
function more_ud_istep_install(): void
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'istep_user_data';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
    id INT NOT NULL AUTO_INCREMENT,
    wp_user_id BIGINT UNSIGNED NOT NULL,
    fonction VARCHAR(255),
    nTelephone VARCHAR(10),
    bureau VARCHAR(4),
    equipe VARCHAR(255),
    rangEquipe VARCHAR(255),
    tourDuBureau VARCHAR(10),
    campus VARCHAR(255),
    employeur VARCHAR(255),
    caseCourrier VARCHAR(10),
    PRIMARY KEY (id),
    FOREIGN KEY (wp_user_id) REFERENCES {$wpdb->prefix}users(ID)
) $charset_collate;";

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sql );
}
register_activation_hook( __FILE__, 'more_ud_istep_install' ); //Appelé lors de l'activation du plugin



add_shortcode('add_istep_user_form','add_new_user_form');

/**
 * Affiche le formulaire de création d'utilisateur
 * @return string
 */
function add_new_user_form():string {
    $html = "<p>Vous n'êtes pas connecté</p>";
    if (can_user_create_users(['informatique','administrator','secretariat']))
    {
        //TODO: Do something
    } else {
        $html = "<p>Vous n'avez pas l'autorisation d'utiliser ceci</p>";
    }
    return $html;

}

// ---- Menu Administrateur ----
/**
 * Génère la page dans le panel administrateur
 * @return void
 */
function more_userdata_istep_menu(): void{
    add_menu_page(
        "Paramètre création d'utilisteur",
        "ISTeP'Users options",
        "administrator",
        "istep_users_options",
        "more_userdata_istep_menu_content"
    );
}
add_action( 'admin_menu', 'more_userdata_istep_menu' );
