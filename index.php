<?php
/*
Plugin Name: More userData for ISTeP
Plugin URI: https://wpusermanager.com/
Description: Ajoute de nouveaux champs à renseigner lors de la création d'un utilisateur pour les membres de l'ISTeP
Author: Robin Simonneau, Arbër Jonuzi
Version: 1.0
Author URI: https://robin-sim.fr/
*/
error_reporting(E_ALL); ini_set('display_errors', '1');
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
register_activation_hook( __FILE__, 'more_ud_istep_install' );

