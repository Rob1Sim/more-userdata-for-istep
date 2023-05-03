<?php
/*
Plugin Name: More userData for ISTeP
Plugin URI: https://wpusermanager.com/
Description: Ajoute un formulaire de création d'utilisateur pensé pour l'ISTeP et une page personalisé pour les utilsateurs, ainsi qu'une gestions des équipes.
!Nécéssite un plugin qui gère les permissions pour les roles!
!l'utilisation de tiny directory est recomandé!
Author: Robin Simonneau, Arbër Jonuzi
Version: 1.0
Author URI: https://robin-sim.fr/
*/
wp_enqueue_style('more-userdata-for-istep',plugins_url('more-userdata-for-istep.css',__FILE__));
wp_enqueue_script('more-userdata-for-istep-js',plugins_url('more-userdata-for-istep.js',__FILE__),array(), false, true);

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
        tourDuBureau VARCHAR(30),
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

    $page_data = array(
        'post_title' => "Membres de l'ISTeP",
        'post_content' => '[users_directory]',
        'post_status' => 'publish',
        'post_type' => 'page',
        'post_name' => 'membres-istep'

    );

// Insère la page dans la base de données de WordPress
    wp_insert_post($page_data);
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
        if(isset($_GET['error'])){
            $html.= "<div class=\"error\">".sanitize_text_field($_GET['error'])."</div>";
        }
        $html =<<<HTML
        <h4>Formulaire de création d'utilisateur</h4>
        <form method="POST" class="create-istep-user-form" action="" id="create-user-istep-form" enctype="multipart/form-data">
            <label for="last_name">Nom : 
                <input type="text" name="last_name" id="last_name" required/> 
            </label>
            
            
            <label for="name">Prénom :
                <input type="text" name="name" id="name" required/> 
             </label>
            
            
            <label for="login">Identifiant : 
                <input type="text" name="login" id="login" required/> 
            </label>
            
            
            <label for="email">Adresse email :
                <input type="email" name="email" id="email" required/>
             </label>
             
             <label for="phone" id="phoneParent">Numéro de téléphone :
                <input type="tel" name="phone" id="phoneNumber"/>
             </label>
            
            <label for="password">Mot de passe : 
                <input type="password" name="password" id="password" required/>
                <button type="button" id="random-pws">Générer un mot de passe aléatoire</button>
                <button type="button" id="show-password">Afficher le mot de passe</button>
            </label>
            
            <label for="office">Bureau : 
                <input type="text" name="office" id="office" required/> 
            </label>
            
            <label for="job">Fonction : 
                <input type="text" name="job" id="job" required/> 
            </label>
            
            <label for="tower" id="tower">Tour du bureau : 
                <ul>
                    <li><label></label><input type="radio" name="tourBureau" value="tour-46-00-2e" checked/>Tour 46 - 00 2ème étage</label> </li>
                    <li><label></label><input type="radio" name="tourBureau" value="tour-46-00-3e" checked/>Tour 46 - 00 3ème étage</label> </li>
                    <li><label></label><input type="radio" name="tourBureau" value="tour-46-00-4e" checked/>Tour 46 - 00 4ème étage</label> </li>
                    <li><label></label><input type="radio" name="tourBureau" value="tour-46-45-2e" checked/>Tour 46 - 45 2ème étage</label> </li>
                    <li><label></label><input type="radio" name="tourBureau" value="tour-56-66-5e" checked/>Tour 56 - 66 5ème étage</label> </li>
                    <li><label></label><input type="radio" name="tourBureau" value="tour-56-55-5e" checked/>Tour 56 - 55 5ème étage</label> </li>
                </ul>
            </label>
            
            <label id="c">Equipe :
            <select name="team" id="team">
                
HTML;
        //Récupères les équipes existantes
        global $wpdb;
        $table_name = TABLE_TEAM_NAME;
        $teams = $wpdb->get_results("SELECT * FROM $table_name");

        foreach ($teams as $team){
            $teamName = $team->nom_equipe;
            $teamId = $team->id_equipe;
            $html .= "<option value=\"".$teamId."\">".$teamName."</option>";
        }
        $html.=<<<HTML
        </select>
        </label>
        <label for="teamRank" >
            Rang au sein de l'équipe :
            <input type="text" name="teamRank" id="teamRank" required>
        </label>
        <label for="campus" >
            Campus :
            <input type="text" name="campus" id="campus" required>
        </label>
        <label for="employer">
            Employeur :
            <input type="text" name="employer" id="employer" required>
        </label>
        <label for="mailCase">
            Case courrier :
            <input type="text" name="mailCase" id="mailCase">
        </label>
        <label>
        Photo de profile :
        <input type="file" accept="image/jpeg, image/png" name="async-upload" >
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
            $html.= '<label><p></p><input type="checkbox" name="roles[]" value="'.$key.'"><p>'.$value['name'].'</p></label><br/>';
        }
        $html.= <<<HTML
        </div>
        <input type="hidden" name="submit_create_istep_user" value="value">
        <button type="submit" name="submit_create_istep_user" id="create-user-submit-btn"">Créer</button>
</form>
HTML;
    } else {
        $html = "<p>Vous n'avez pas l'autorisation d'utiliser ceci</p>";
    }
    return $html;

}

add_action('wp','add_new_user');
/**
 * Fonction qui vérifie les données entré dans le formulaire et les enregistre dans la base de donnée
 * @return void
 */
function add_new_user() {
    $current_url = home_url( "sample-page/?" );
    //Affiche une erreur si des informations entréer sont incorrecte
    if (isset($_GET['user-create-error'])){
        $error = sanitize_text_field($_GET['user-create-error']);
        switch ($error) {
            case "1":
                echo "<div class=\"user-create-error\">Le numéro de téléphone est incorrecte</div>";
                break;
            case "2":
                echo "<div class=\"user-create-error\">Erreur lors de la création de l'utilisateur : ".sanitize_text_field($_GET["error-message"])."</div>";
                break;
            case "3":
                echo "<div class=\"user-create-error\">Erreur lors de l'ajout des rôles</div>";
                break;
            case "4":
                echo "<div class=\"user-create-error\">Erreur lors de l'ajout de l'avatar : ".sanitize_text_field($_GET["error-message"])."</div>";
                break;
            case "5":
                echo "<div class=\"user-create-error\">Le format de l'image n'est pas correcte</div>";
                break;
            case "6":
                echo "<div class=\"user-create-error\">L'extension de l'image n'est pas correcte</div>";
                break;
        }

    }
    //affiche un succès si l'utilisateur est bien ajouté
    if (isset($_GET['user-create-success'])){
        echo "<div class=\"user-create-success\">L'utilisateur à été ajouté avec succès</div>";
    }
    if (isset($_POST['submit_create_istep_user'])) {
        // Récupération des données du formulaire
        $last_name = sanitize_text_field($_POST['last_name']);
        $name = sanitize_text_field($_POST['name']);
        $login = sanitize_text_field($_POST['login']); //Bloquer les nom existant
        $email = sanitize_text_field($_POST['email']);// Bloquer les email existant
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
        $pp = $_FILES['async-upload'];

        if (isset($_POST['roles'])){
            //Nettoyage des roles
            $roles = $_POST['roles'];
            $verified_roles = [];
            foreach ($roles as $role){
                $verified_roles[] = sanitize_text_field($role);
            }
        } else {
            $verified_roles = [get_option('default_role')];
        }

        // Validation des données
        if (isset($last_name) && isset($name) && isset($login) && isset($email)
            && isset($password) && isset($office) && isset($job) && isset($tourBureau) && isset($team)
            && isset($teamRank) && isset($campus) && isset($employer)) {

            if (strlen($phone)!=10){

                wp_redirect($current_url."user-create-error=1");
            }

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
                wp_redirect($current_url."user-create-error=2&error-message=$error_message");

            } else {

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
                    wp_redirect($current_url);
                    exit;
                }else{
                    $user_data = array(
                        'ID' => $user_id,
                        'roles' => $verified_roles
                    );
                    //Ajout des roles à l'utilisateur créé
                    if (is_wp_error(wp_update_user( $user_data ))){
                        wp_redirect($current_url."user-create-error=3");
                    }

                    //Ajout de l'image de profile
                    createPersonalPage($user_id,$name." ".$last_name,strtolower($last_name)."_".strtolower($name));
                    if (isset($_FILES['async-upload']["name"]) && $_FILES['async-upload']["name"]!== ""){
                        // Vérifie si le fichier est au format JPG, PNG ou GIF
                        $allowed_formats = array('jpg', 'jpeg', 'png', 'gif');
                        $extension = strtolower(pathinfo($_FILES['async-upload']['name'], PATHINFO_EXTENSION));

                        if(!in_array($extension, $allowed_formats)) {
                            wp_redirect($current_url."user-create-error=6");

                        } else {
                            require_once(ABSPATH . 'wp-admin/includes/media.php');
                            require_once(ABSPATH . 'wp-admin/includes/file.php');
                            require_once(ABSPATH . 'wp-admin/includes/image.php');
                            $attachment_id = media_handle_upload('async-upload', 0);
                            if(is_wp_error($attachment_id)) {
                                wp_redirect($current_url."user-create-error=4&error-message=". $attachment_id->get_error_message());
                            } else {
                                // Mettez à jour le champ de méta de l'utilisateur avec l'ID de l'attachement
                                add_user_meta($user_id,"wp_user_avatar",$attachment_id);
                            }

                            wp_redirect($current_url."user-create-success=0",302);
                        }


                    }else{
                        wp_redirect($current_url."user-create-success=0",302);

                    }
                }

            }
        }
    }
}

/**
 * Créer une page personnel lors de l'ajout d'un utilisateur via le formulaire
 * @param $userId
 * @param $userDisplayName
 * @param $userNiceName
 * @return void
 */
function createPersonalPage($userId,$userDisplayName,$userNiceName){
    $parent = get_page_by_path('membres-istep');

    $page_data = array(
        'post_title' => $userDisplayName,
        'post_content' => '',
        'post_status' => 'publish',
        'post_type' => 'page',
        'post_author' => $userId,
        'post_name' => $userNiceName,
        'post_parent' => $parent->ID,

    );

// Insère la page dans la base de données de WordPress
    wp_insert_post($page_data);
}
