<?php
/**
 * Gère le formulaire de création d'utilisateur
 */
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

                    //Création de la page perso
                    $wpdb->insert(TABLE_PERSONAL_PAGE_NAME,array(
                        "wp_user_id"=>$user_id,
                    ));

                    //Ajout de l'image de profile
                    create_personal_page($name." ".$last_name,$login);
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