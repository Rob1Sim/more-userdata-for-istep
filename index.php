<?php
/*
Plugin Name: More userData for ISTeP
Plugin URI: https://wpusermanager.com/
Description: Ajoute un formulaire de création d'utilisateur pensé pour l'ISTeP, ainsi qu'une gestions des équipes.
Author: Robin Simonneau, Arbër Jonuzi
Version: 1.0
Author URI: https://robin-sim.fr/
*/
wp_enqueue_style('more-userdata-for-istep',plugins_url('more-userdata-for-istep.css',__FILE__));

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
        nom_equipe VARCHAR(255) NOT NULL,
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
        FOREIGN KEY (wp_user_id) REFERENCES {$wpdb->prefix}users(ID)
            ON DELETE CASCADE,
        FOREIGN KEY (equipe) REFERENCES {$wpdb->prefix}equipe_ISTeP(id_equipe)
            ON DELETE SET NULL
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
        <h4>Formulaire de création d'utilisateur</h4>
        <form method="POST" class="create-istep-user-form">
            <label for="last_name">Nom : 
                <input type="text" name="last_name" required/> 
            </label>
            
            
            <label for="name">Prénom :
                <input type="text" name="name" required/> 
             </label>
            
            
            <label for="login">Identifiant : 
                <input type="text" name="login" required/> 
            </label>
            
            
            <label for="email">Adresse email :
                <input type="email" name="email" required/>
             </label>
             
             <label for="phone">Numéro de téléphone :
                <input type="tel" name="phone"/>
             </label>
            
            <label for="password">Mot de passe : 
                <input type="password" name="password" required/>
            </label>
            
            <label for="office">Bureau : 
                <input type="text" name="office" required/> 
            </label>
            
            <label for="job">Fonction : 
                <input type="text" name="job" required/> 
            </label>
            
            <label for="login">Tour du bureau : 
                <ul>
                    <li><label></label><input type="radio" name="tourBureau" value="tour-46-00-2e" checked/>Tour 46 - 00 2ème étage</label> </li>
                    <li><label></label><input type="radio" name="tourBureau" value="tour-46-00-3e" checked/>Tour 46 - 00 3ème étage</label> </li>
                    <li><label></label><input type="radio" name="tourBureau" value="tour-46-00-4e" checked/>Tour 46 - 00 4ème étage</label> </li>
                    <li><label></label><input type="radio" name="tourBureau" value="tour-46-45-2e" checked/>Tour 46 - 45 2ème étage</label> </li>
                    <li><label></label><input type="radio" name="tourBureau" value="tour-56-66-5e" checked/>Tour 56 - 66 5ème étage</label> </li>
                    <li><label></label><input type="radio" name="tourBureau" value="tour-56-55-5e" checked/>Tour 56 - 55 5ème étage</label> </li>
                </ul>
            </label>
            
            <label>Equipe :
            <select name="team">
                
HTML;
        //Récupères les équipes existantes
        global $wpdb;
        $table_name = TABLE_TEAM_NAME;
        $teams = $wpdb->get_results("SELECT * FROM $table_name");

        foreach ($teams as $team){
            $teamName = $team->nom_equipe;
            echo $teamName;
            $teamId = $team->id_equipe;
            $html .= "<option value=\"".$teamId."\">".$teamName."</option>";
        }
        $html.=<<<HTML
        </select>
        </label>
        <label for="teamRank">
            Rang au sein de l'équipe :
            <input type="text" name="teamRank" required>
        </label>
        <label for="campus">
            Campus :
            <input type="text" name="campus" required>
        </label>
        <label for="employer">
            Employeur :
            <input type="text" name="employer" required>
        </label>
        <label for="mailCase">
            Case courrier :
            <input type="text" name="mailCase">
        </label>
        <label>
        Photo de profile :
        <input type="file" accept="image/jpeg, image/png" name="pp">
        </label>
        <div class="role-box">
HTML;
        if ( ! function_exists( 'get_editable_roles' ) ) {
            require_once ABSPATH . 'wp-admin/includes/user.php';
        }
        $roles = get_editable_roles();

        // Récupère les rôles sélectionnés dans la base de données
        $selected_roles = get_option('istep_user_roles', array());

        // Affiche une checkbox pour chaque rôle
        foreach ($roles as $key => $value) {
            $html.= '<label><p></p><input type="checkbox" name="roles[]" value="'.$key.'" '.checked(in_array($key, $selected_roles), true, false).'><p>'.$value['name'].'</p></label><br/>';
        }
        $html.= <<<HTML
        </div>
        <button type="submit" name="submit">Créer</button>
</form>
HTML;


    } else {
        $html = "<p>Vous n'avez pas l'autorisation d'utiliser ceci</p>";
    }
    return $html;

}

function add_new_user() {
    if (isset($_POST['submit'])) {
        // Récupération des données du formulaire
        $last_name = sanitize_text_field($_POST['last_name']);
        $name = sanitize_text_field($_POST['name']);
        $login = sanitize_text_field($_POST['login']);
        $email = sanitize_text_field($_POST['email']);
        $phone = sanitize_text_field($_POST['phone']);
        $password = $_POST['password'];
        $office = sanitize_text_field($_POST['office']);
        $job = sanitize_text_field($_POST['job']);
        $tourBureau = sanitize_text_field($_POST['tourBureau']);
        $team = sanitize_text_field($_POST['team']);
        $teamRank = sanitize_text_field($_POST['teamRank']);
        $campus = sanitize_text_field($_POST['campus']);
        $employer = sanitize_text_field($_POST['employer']);
        $mailCase = sanitize_text_field($_POST['mailCase']);
        $pp = $_FILES['pp'];
        $roles = $_POST['roles'];
        //Nettoyage des roles
        $verified_roles = [];
        foreach ($roles as $role){
            $verified_roles[] = sanitize_text_field($role);
        }

        // Validation des données (à faire)
        if (isset($last_name) && isset($name) && isset($login) && isset($email)
            && isset($password) && isset($office) && isset($job) && isset($tourBureau) && isset($team)
            && isset($teamRank) && isset($campus) && isset($employer)) {

            // Créer un tableau avec les informations de l'utilisateur
            $user_data = array(
                'user_login' => $login,
                'user_email' => $email,
                'user_pass'  => $password,
                'user_nicename' => strtolower($last_name)."_".strtolower($name),
                'display_name' => $last_name." ".$name,
            );

            //Ajout de l'utilisateur
            $user_id = wp_insert_user( $user_data );

            // Vérifier si l'utilisateur a été ajouté avec succès
            if ( is_wp_error( $user_id ) ) {
                $error_message = $user_id->get_error_message();
                echo "Erreur lors de l'ajout de l'utilisateur : $error_message";
            } else {
                echo "L'utilisateur a été ajouté avec succès avec l'ID : $user_id";

                //Si l'utilisateur wp a bien été créer on continue
                global $wpdb;
                $user = get_user_by( 'login', $login ); // récupère l'utilisateur par login
                $user_id = $user->ID;
                $data = array(
                    'wp_user_id' => $user_id,
                    'fonction' => $job,
                    'nTelephone' => $phone,
                    'bureau' => $office,
                    'equipe' => intval($team),
                    'rangEquipe' => $teamRank,
                    'tourDuBureau' => $tourBureau,
                    'campus' => $campus,
                    'employeur' => $employer,
                    'caseCourrier' => $mailCase,
                );
                $format = array(
                    '%s',
                    '%s',
                    '%s',
                    '%d',
                    '%s',
                    '%s',
                    '%s',
                    '%s',
                    '%s',
                );
                //Ajout de l'utilisateur dans la bd membre_ISTeP
                if ($wpdb->insert(TABLE_MEMBERS_NAME, $data, $format ) === false) {
                    $error_message = $wpdb->last_error;
                    echo "Une erreur est survenue lors de l'ajout de l'utisateur : ".$error_message;
                }else{
                    $user_data = array(
                        'ID' => $user_id,
                        'role' => $verified_roles
                    );
                    //Ajout des roles à l'utilisateur créer
                    wp_update_user( $user_data );


                }

            }

        }
    }
}



