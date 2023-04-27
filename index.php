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

/**
 * Gère le contenue de la page administrateur
 * @return void
 */
function more_userdata_istep_menu_content(): void {
    // Vérifie si le formulaire a été soumis
    if (isset($_POST['submit'])) {
        // Met à jour les options avec les rôles sélectionnés
        update_option('istep_user_roles', $_POST['istep_user_roles']);
        echo '<div id="message" class="updated notice"><p>Rôles mis à jour avec succès.</p></div>';
    }
    ?>
    <div class="wrap">
        <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
        <form method="post" action="">
            <?php wp_nonce_field( 'istep_user_roles_nonce', 'istep_user_roles_nonce' ); ?>
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="istep_user_roles"><?php _e( 'Rôles qui peuvent créer des utilisateurs:', 'istep_users' ); ?></label></th>
                    <td>
                        <?php
                        // Récupère tous les rôles WordPress
                        $roles = get_editable_roles();

                        // Récupère les rôles sélectionnés dans la base de données
                        $selected_roles = get_option('istep_user_roles', array());
                        // Affiche une checkbox pour chaque rôle
                        foreach ($roles as $key => $value) {
                            echo '<label><input type="checkbox" name="istep_user_roles[]" value="'.$key.'" '.checked(in_array($key, $selected_roles), true, false).'>'.$value['name'].'</label><br/>';
                        }
                        ?>
                    </td>
                </tr>
            </table>
            <?php submit_button('Enregistrer les rôles', 'primary', 'submit', true); ?>
        </form>
    </div>
    <?php
}