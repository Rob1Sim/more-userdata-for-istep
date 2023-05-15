<?php
/*
Plugin Name: More userData for ISTeP
Plugin URI: https://wpusermanager.com/
Description: Ajoute un formulaire de création d'utilisateur pensé pour l'ISTeP et une page personalisé pour les utilsateurs, ainsi qu'une gestions des équipes.
!Nécéssite un plugin qui gère les permissions pour les roles!
Plus d'informations dans le fichier README.md
Author: Robin Simonneau
Version: 1.0
Author URI: https://robin-sim.fr/
*/
wp_enqueue_style('more-userdata-for-istep',plugins_url('styles/more-userdata-for-istep.css',__FILE__));
wp_enqueue_script('more-userdata-for-istep-js',plugins_url('scripts/more-userdata-for-istep.js',__FILE__),array(), false, true);

require_once(plugin_dir_path(__FILE__).'utilities.php');
require_once( plugin_dir_path( __FILE__ ) . 'admin-functions.php' );

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

";

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

}
register_activation_hook( __FILE__, 'more_ud_istep_install' ); //Appelé lors de l'activation du plugin



add_shortcode('add_istep_user_form','add_new_user_form');

/**
 * Affiche le formulaire de création d'utilisateur
 * @return string
 */
function add_new_user_form():string {
    $html = "<p>Vous n'êtes pas connecté</p>";
    if (can_user_access_this(get_option('istep_user_roles')))
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
                <input type="password" name="password" id="password" autocomplete required/>
                <div class="password-btn">
                    <button type="button" id="random-pws">Générer un mot de passe aléatoire</button>
                    <button type="button" id="show-password">Afficher le mot de passe</button>  
                </div>
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
             <div class="role-box">   
HTML;
        //Récupères les équipes existantes
        $teams = get_list_of_table(TABLE_TEAM_NAME);

        foreach ($teams as $team){
            $team_name = $team->nom_equipe;
            $team_id = $team->id_equipe;
            $html.= '<label><p></p><input type="checkbox" name="teams[]" value="'.$team_id.'"><p>'.$team_name.'</p></label><br/>';
        }
        $html.=<<<HTML
        </div>
        </label>
        <label for="teamRank" >
            Rang au sein de l'équipe :
            <input type="text" name="teamRank" id="teamRank" required>
        </label>
        <label for="campus" >
            Campus :
            <select name="campus" id="campus">
HTML;
        $campus = get_list_of_table(TABLE_LOCATION_NAME);
        foreach ($campus as $one_campus){
            $html.= "<option value=\"".$one_campus->id_localisation."\">".$one_campus->nom_localisation."</option>";
        }
        $html .= <<<HTML
           </select>
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
            $checked ="";
            if ($key == get_option("default_role")){
                $checked ="checked";
            }
            $html.= '<label><p></p><input type="checkbox" name="roles[]" value="'.$key.'"'.$checked.'><p>'.$value['name'].'</p></label><br/>';
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
    $current_url = home_url( get_option('default_redirect_link')."/?" );
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
            case "7":
                echo "<div class=\"user-create-error\">La localisation entrée n'éxiste pas</div>";
                break;
        }

    }
    //affiche un succès si l'utilisateur est bien ajouté
    if (isset($_GET['user-create-success'])){
        echo "<div class=\"user-create-success\">L'utilisateur à été ajouté avec succès</div>";
    }
    if (isset($_POST['submit_create_istep_user'])) {
        global $wpdb;
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

        if (isset($_POST['teams'])){
            //Nettoyage des équipes
            $teams = $_POST['teams'];
            $verified_teams = [];
            foreach ($teams as $team){
                $verified_teams[] = sanitize_text_field($team);
            }

        }

        // Validation des données
        if (isset($last_name) && isset($name) && isset($login) && isset($email)
            && isset($password) && isset($office) && isset($job) && isset($tourBureau) && isset($team)
            && isset($teamRank) && isset($campus) && isset($employer)) {

            if (strlen($phone)!=10){

                wp_redirect($current_url."user-create-error=1");
                exit();
            }

            //Vérification de l'éxistance du campus
            $table_name = TABLE_LOCATION_NAME;
            $is_location_existing = "SELECT * FROM $table_name WHERE id_localisation = $campus";
            $results = $wpdb->get_results($is_location_existing);
            if (empty($results)) {
                wp_redirect($current_url."user-create-error=7");
                exit();
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
                $user = get_user_by( 'login', $login ); // récupère l'utilisateur par login
                $user_id = $user->ID;


                $data = array(
                    'wp_user_id' => $user_id,
                    'fonction' => $job,
                    'nTelephone' => $phone,
                    'bureau' => $office,
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
                    $new_user = get_user_by('id', $user_id);
                    foreach ($verified_roles as $new_role){
                        $new_user->add_role($new_role);
                    }
                    $last_member = get_istep_user_by_id($user_id);

                    add_data_to_team_members($verified_teams, $last_member->id_membre);


                    //Ajout de l'image de profile
                    create_personal_page($user_id,$name." ".$last_name,strtolower($last_name)."_".strtolower($name));
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

add_shortcode('istep_user_data','display_users_data');
/**
 * Affiche diverses informations de l'utilisateur sur la page de base
 * @return string
 */
function display_users_data(): string
{
    $page_id = get_queried_object_id();
    $page_author_id = get_post_field( 'post_author', $page_id );
    $page_author_info = get_userdata( $page_author_id ); // Récupère les informations de l'utilisateur

    $userData = get_istep_user_by_id($page_author_id);
    $userAvatar = get_user_avatar($page_author_id);
    $userTower = convert_tower_into_readable($userData->tourDuBureau);
    $userTeams = get_user_teams_names_by_user_id($userData->id_membre);





    $html = <<<HTML
    <div class="user-info-container">
        <div>
            $userAvatar
        </div>
        <div class="user-info-text-container">
            <div>
                <h5>Fonction</h5>
                <p>$userData->fonction</p>
                
                <h5>Equipes</h5>
HTML;
    foreach ($userTeams as $userTeam) {
        $html.="<p>$userTeam->nom_equipe</p>";
    }
    $html.= <<<HTML
            </div>
            <div>
                <h5>Coordonées :</h5>
                <p><strong>Téléphone : </strong><a href="tel:$userData->nTelephone">$userData->nTelephone</a></p>
                <p><strong>Email : </strong><a href="mailto:$page_author_info->user_email">$page_author_info->user_email</a> </p>
                <p><strong>Campus : </strong>$userData->campus</p>
                <p><strong>Tour :</strong>$userTower</p>
                <p><strong>Bureau :</strong>$userData->bureau</p>
            </div>
        </div>
</div>
HTML;
    return $html;
}

/**
 * Créer une page personnel lors de l'ajout d'un utilisateur via le formulaire
 * @param int $userId
 * @param string $userDisplayName
 * @param string $userNiceName
 * @return void
 */
function create_personal_page(int $userId, string $userDisplayName,string $userNiceName): void
{
    $parent = get_page_by_path('membres-istep');

    $page_data = array(
        'post_title' => $userDisplayName,
        'post_content' => '[istep_user_data]',
        'post_status' => 'publish',
        'post_type' => 'page',
        'post_author' => $userId,
        'post_name' => $userNiceName,
        'post_parent' => $parent->ID,

    );

// Insère la page dans la base de données de WordPress
    wp_insert_post($page_data);
}

// -- Tiny Directory --
add_shortcode('users_directory', 'create_directory_from_DB_users');

/**
 * Récupère les utilisateurs dans la base de donnée et les affect à un tableau HTML
 * @return string Le tableau HTML
 */
function create_directory_from_DB_users( $atts ): string{

    if ( ! function_exists( 'get_editable_roles' ) ) {
        require_once ABSPATH . 'wp-admin/includes/user.php';
    }
    $roles = get_editable_roles();

    //Gestions des paramètres
    $list_parameters = shortcode_atts( array(
        'role' => '',
    ), $atts );

    $role_parameter = $list_parameters['role'];
    $role_parameter = strtolower(sanitize_text_field($role_parameter));
    //Si le role n'éxiste pas alors on ne trie pas
    if (!isset($role_parameter) ||$role_parameter == "" ||$roles[$role_parameter] == null){
        $role_parameter = "";
    }



    //Ajout de la feuille de style et du javascript
    wp_enqueue_style('tiny-directory-css',plugins_url('styles/tiny-directory.css',__FILE__));
    wp_enqueue_script('tiny-directory-js',plugins_url('scripts/tiny-directory.js',__FILE__),array(), false, true);
    $users = get_list_of_table(TABLE_MEMBERS_NAME);
    // Vérifier s'il y a des utilisateurs
    if ( !empty( $users ) ) {
        //Génère le tableau
        $html =
            <<<HTML
<div class="tiny-directory-div">
    <label for="search-input-members">Rechercher : </label>
    <input type="hidden" value="$role_parameter" id="role-parameter">
    <input type="text" id="search-input-members"" placeholder="Robin...">
            <select id="select-role">
HTML;



        foreach ($roles as $key => $value){
            $html.= "<option value=\"".$key."\">".$value['name']."</option>";
        }
        $html .= <<<HTML
        </select>
    <div class="scrollable-div">
    <table class="tiny-directory-table">
    <thead >
        <tr class="tiny-directory-th">
            <th class="tiny-directory-th" colspan="1">NOM / Prénom</th>
            <th class="tiny-directory-th" colspan="1">Email</th> 
            <th class="tiny-directory-th" colspan="1">Téléphone</th> 
            <th class="tiny-directory-th" colspan="1">Fonction</th> 

    </thead>
    <tbody>
    HTML;
        foreach ( $users as $user ) {
            $userID = $user->wp_user_id;
            $userAvatar = get_user_avatar($userID);
            $istep_users = get_istep_user_by_id($userID);
            $wp_user = get_user_by("id",$userID);
            $linkToProfilePage = home_url()."/membres-istep/$wp_user->user_nicename";

            $users_roles = $wp_user->roles;
            $users_roles_str = implode("-",$users_roles);
            $tower = convert_tower_into_readable($istep_users->tourDuBureau);
            $campus = get_name_of_location_by_id(intval($istep_users->campus));
            $html.= <<<HTML
        <tr class="user-$userID tiny-directory-tr" tabindex="0">
            
            <td class="no-display-fields" id="pp-$userID" data-id="$userID">$userAvatar
                <input type="hidden" value="$users_roles_str" id="input-roles"/>
            </td>
            <td class="no-display-fields" id="login-$userID">$linkToProfilePage</td>
            <td class="tiny-directory-td name-$userID">$wp_user->display_name</td>
            <td class="tiny-directory-td email-$userID"><a href="mailto:$wp_user->user_email">$wp_user->user_email</td>
            <td class="tiny-directory-td phone-$userID">$istep_users->nTelephone</td>
            <td class="tiny-directory-td"$userID">
            $istep_users->fonction
            </td>
            <td class="no-display-fields campus-$userID">
                $campus
            </td>
            <td class="no-display-fields tower-$userID">
                $tower
            </td>
            <td class="no-display-fields office-$userID">
                $istep_users->bureau
            </td>
        </tr>
HTML;
        }
        $html.= <<<HTML
    </tbody>
</table>
</div>
</div>
HTML;
        return $html;
    } else {
        return "Error no users has been found :(";
    }
}
