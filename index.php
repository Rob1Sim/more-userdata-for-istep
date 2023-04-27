<?php
/*
Plugin Name: More userData for ISTeP
Plugin URI: https://wpusermanager.com/
Description: Ajoute de nouveaux champs à renseigner lors de la création d'un utilisateur pour les membres de l'ISTeP,
ainsi qu'un formulaire de création d'utilistateur.
Author: Robin Simonneau, Arbër Jonuzi
Version: 1.0
Author URI: https://robin-sim.fr/
*/
require_once(plugin_dir_path(__FILE__).'utilities.php');
require_once( plugin_dir_path( __FILE__ ) . 'admin-functions.php' );

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
 * Créer la base de donnée lors de l'activation du plugin
 * @return void
 */
function more_ud_istep_install(): void
{
    global $wpdb;
    $table_name_user_data = TABLE_MEMBERS_NAME;
    $table_name_user_team = TABLE_TEAM_NAME;
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "
    CREATE TABLE $table_name_user_team(
        id_equipe INT NOT NULL AUTO_INCREMENT,
        nom_equipe VARCHAR(255),
        PRIMARY KEY(id_equipe)
    )$charset_collate;
    CREATE TABLE $table_name_user_data (
        id_membre INT NOT NULL AUTO_INCREMENT,
        wp_user_id BIGINT UNSIGNED NOT NULL,
        fonction VARCHAR(255),
        nTelephone VARCHAR(10),
        bureau VARCHAR(4),
        equipe INT,
        rangEquipe VARCHAR(255),
        tourDuBureau VARCHAR(10),
        campus VARCHAR(255),
        employeur VARCHAR(255),
        caseCourrier VARCHAR(10),
        PRIMARY KEY (id_membre),
        FOREIGN KEY (wp_user_id) REFERENCES {$wpdb->prefix}users(ID),
        FOREIGN KEY (equipe) REFERENCES {$wpdb->prefix}equipe_ISTeP(id_equipe)
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
    if (can_user_create_users(get_option('istep_user_roles')))
    {
        $html =<<<HTML
        <form method="POST">
            <label for="last_name">Nom : 
                <input type="text" name="last_name"/> 
            </label>
            
            
            <label for="name">Prénom :
                <input type="text" name="name"/> 
             </label>
            
            
            <label for="login">Identifiant : 
                <input type="text" name="login"/> 
            </label>
            
            
            <label for="email">Adresse email :
                <input type="email" name="email"/>
             </label>
             
             <label for="phone">Numéro de téléphone :
                <input type="tel" name="phone"/>
             </label>
            
            <label for="password">Mot de passe : 
                <input type="password" name="password"/>
            </label>
            
            <label for="office">Bureau : 
                <input type="text" name="office"/> 
            </label>
            
            <label for="login">Tour du bureau : 
                <ul>
                    <li><input type="radio" name="tourBureau" value="Tour 46 - 00 2ème étage" checked/> </li>
                    <li><input type="radio" name="tourBureau" value="Tour 46 - 00 3ème étage"/> </li>
                    <li><input type="radio" name="tourBureau" value="Tour 46 - 00 4ème étage"/> </li>
                    <li><input type="radio" name="tourBureau" value="Tour 46 - 45 2ème étage"/> </li>
                    <li><input type="radio" name="tourBureau" value="Tour 56 - 66 5ème étage"/> </li>
                    <li><input type="radio" name="tourBureau" value="Tour 56 - 55 5ème étage"/> </li>
                </ul>
            </label>
            
            <label>Equipe : </label>
              <select name="team">
                <option value="Pétrologie et Géodynamique">Pétrologie et Géodynamique</option>
                <option value="Tectonique">Tectonique</option>
                <option value="Terre-Mer Structures et Archives">Terre-Mer Structures et Archives</option>
                <option value="Informatique">Informatique</option>
                <option value="Direction">Direction</option>
                <option value="Terre-Mer Structures et Archives">Terre-Mer Structures et Archives</option>
                <option value="Terre-Mer Structures et Archives">Terre-Mer Structures et Archives</option>
                <option value="Pas d'équipe">Pas d'équipe</option>
              </select>

HTML;
        $roles = get_editable_roles();
        // Récupère les rôles sélectionnés dans la base de données
        $selected_roles = get_option('istep_user_roles', array());
        // Affiche une checkbox pour chaque rôle
        foreach ($roles as $key => $value) {
            $html.= '<label><input type="checkbox" name="roles" value="'.$key.'" '.checked(in_array($key, $selected_roles), true, false).'>'.$value['name'].'</label><br/>';
        }

    } else {
        $html = "<p>Vous n'avez pas l'autorisation d'utiliser ceci</p>";
    }
    return $html;

}





