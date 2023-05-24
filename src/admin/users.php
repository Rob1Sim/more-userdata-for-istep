<?php
/**
 * Gestion de la listes des utilisateur
 */
wp_enqueue_script('more-userdata-for-istep-admin-js',plugins_url('../../scripts/more-userdata-for-istep-admin.js',__FILE__),array(), false, true);


/**
 * Affiche toutes les informations des utilisateurs de l'ISTeP
 * @return void
 */
function more_userdata_istep_users_list():void{
    if ( !can_user_access_this(get_option('admin_user_roles')) ) {
        wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
    }
?><div class="wrap">
        <h1>Liste des membres de l'ISTeP</h1>
        <label for="dropdown-colonne">Trier par :</label>
        <select id="dropdown-colonne">
            <option value="0">ID</option>
            <option value="1">Nom de l'utilisateur</option>
            <option value="2">Login</option>
            <option value="3">Equipe</option>
            <option value="4">Fonction</option>
            <option value="5">Email</option>
            <option value="6">Numéro de téléphone</option>
            <option value="7">Rang dans l'équipe</option>
            <option value="8">Tour du bureau</option>
            <option value="9">Bureau</option>
            <option value="10">Campus</option>
            <option value="11">Employeur</option>
            <option value="12">Case courrier</option>
        </select>

        <label for="search">Rechercher :</label>
        <input type="text" id="search">
        <table class="wp-list-table widefat fixed striped " id="istep-users-list">
            <thead>
            <tr>
                <th>ID</th>
                <th>Nom de l'utilisateur</th>
                <th>Login</th>
                <th>Equipes</th>
                <th>Fonction</th>
                <th>Email</th>
                <th>Numéro de téléphone</th>
                <th>Rang dans l'équipe</th>
                <th>Tour du bureau</th>
                <th>Bureau</th>
                <th>Campus</th>
                <th>Employeur</th>
                <th>Case courrier</th>
                <th>Modifier</th>
                <th>Supprimer</th>
            </tr>
            </thead>
            <tbody>
<?php
            $users = get_list_of_table(TABLE_MEMBERS_NAME);
            foreach ($users as $user) {
                $teams = get_user_teams_names_by_user_id($user->id_membre);
                $wp_user = get_userdata( $user->wp_user_id );
                echo '<tr>';
                echo '<td>' . $user->id_membre . '</td>';
                echo '<td>' . $wp_user->display_name . '</td>';
                echo '<td>' . $wp_user->user_login . '</td>';
                echo '<td>';
                foreach ($teams as $team){
                    echo $team.', ';
                }
                echo '</td>';
                echo '<td>' . $user->fonction . '</td>';
                echo '<td>' . $wp_user->user_email . '</td>';
                echo '<td>' . $user->nTelephone . '</td>';
                echo '<td>' . $user->rangEquipe . '</td>';
                echo '<td>' . convert_tower_into_readable($user->tourDuBureau) . '</td>';
                echo '<td>' . $user->bureau . '</td>';
                echo '<td>' . get_name_of_location_by_id(intval($user->campus)) . '</td>';
                echo '<td>' . $user->employeur . '</td>';
                echo '<td>' . $user->caseCourrier . '</td>';
                echo '<td>
                        <form method="post" action="' . admin_url( 'admin.php?page=modify_users_data&id=' . $user->id_membre ) . '">
                            <input type="hidden" name="id" value="' . $user->id_membre . '">
                            <button type="submit" class="button">Modifier</button>
                        </form>
                      </td>';
                echo '<td>
                        <form method="post" action="' . admin_url( 'admin.php?page=erase_user&id=' . $user->id_membre ) . '">
                            <input type="hidden" name="user_delete_id" value="' . $user->id_membre . '">
                            <button type="submit" class="button button-primary" style="background: #d0021b; border-color: #d0021b">Supprimer</button>
                        </form>
                      </td>';
                echo '</tr>';
            }
            ?></tbody>
        </table>
    </div><?php
}

/**
 * Formulaire de modification de l'équipe d'un utilisateur
 * @return void
 */
function more_userdata_istep_users_edit_data():void
{
    if ( ! current_user_can( ADMIN_CAPACITY ) ) {
        wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
    }
    if ( isset($_POST['changeTeams']) &&( isset($_POST['idUser']))) {
        $id_user = sanitize_text_field($_POST['idUser']);
        $last_name = sanitize_text_field($_POST['last_name']);
        $first_name = sanitize_text_field($_POST['first_name']);
        $function = sanitize_text_field($_POST['function']);
        $rank = sanitize_text_field($_POST['rank']);
        $employer = sanitize_text_field($_POST['employer']);
        $mailCase = sanitize_text_field($_POST['mailCase']);
        $campus = sanitize_text_field($_POST['campus']);
        $office_tower = sanitize_text_field($_POST['tourBureau']);
        $office = sanitize_text_field($_POST['office']);
        $phone = sanitize_text_field($_POST['phone']);
        $wp_id = sanitize_text_field($_POST['idUserWp']);

        if(isset($last_name) && isset($first_name) && isset($function) && isset($rank)
            && isset($employer) && isset($mailCase) && isset($campus) && isset($office_tower) && isset($office) && isset($phone)){

            //Vérification du campus
            if(!is_location_existing($campus)){
                echo '<div id="message" class="notice notice-error">Le campus n\'existe pas !</div>';
                edit_user_form();
                exit();
            }

            //Vérification du téléphone
            if (strlen($phone)!=10){
                echo '<div id="message" class="notice notice-error">Le numéro de téléphone est incorrecte !</div>';
                edit_user_form();
                exit();
            }
            //Vérification de l'employeur
            if ($employer !== "sorbonne-universite" && $employer != "cnrs" && $employer != "aucun"){

                echo '<div id="message" class="notice notice-error">L\'employeur n\'existe pas !</div>';
                edit_user_form();
                exit();
            }
            //Mis à jour de l'utilisateur
            global $wpdb;
            $wpdb->update(TABLE_MEMBERS_NAME,array(
                "fonction"=>$function,
                "caseCourrier"=>$mailCase,
                "employeur"=>$employer,
                "rangEquipe"=>$rank,
                "nTelephone" => $phone,
                "tourDuBureau" => $office_tower,
                "bureau"=>$office,
                "campus_location"=>$campus
            ),array(
                "wp_user_id"=>$wp_id
            ));
            $display_name = $last_name ." ".$first_name;
            $user_data = get_userdata($wp_id);

            $user_data->display_name = $display_name;

            $result = wp_update_user($user_data);

            if (is_wp_error($result)) {
                $error_message = $result->get_error_message();
                wp_redirect($error_url."user-update-error=7?error-message=".$error_message);
            }
        }
        //Vérification des équipes
        $verified_teams = [];
        $already_exist_data= get_user_teams_id_by_user_id($id_user);
        $teams_already_in = [];
        if (!isset($_POST['teams'])){
            $verified_teams[] = 1;
        }else {
            $teams = $_POST['teams'];
            foreach ($teams as $team){
                //Si l'équipe n'éxiste pas on l'ajoute
                if (!in_array($team,$already_exist_data)){
                    $verified_teams[] = sanitize_text_field($team);
                }else{
                    $teams_already_in[] = sanitize_text_field($team);
                }
            }
        }

        //On récupère les tables qui n'était pas coché mais présent dans les tables de l'utilisateur
        $teams_to_delete = array_diff($already_exist_data,$teams_already_in);
        foreach ($teams_to_delete as $team){
            //on les supprime
            delete_data_from_team_members($team,sanitize_text_field($id_user));
        }
        //on ajoute les nouvelle
        add_data_to_team_members($verified_teams,$id_user);


        echo '<div id="message" class="updated notice"><p>Équipe Mis à jour avec succès.</p></div>';
        echo '<a href="'.admin_url("admin.php?page=istep_users_list").'">Retour à la liste</a>';
    }else{
        edit_user_form();
    }


}

/**
 * Supprime l'utilisateur passé dans la requête
 * @return void
 */
function more_userdata_istep_users_delete_user():void{
    if ( !can_user_access_this(get_option('admin_user_roles')) ) {
        wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
    }
    if ( current_user_can( ADMIN_CAPACITY ) && isset($_POST['user_delete_id']) ){
        $id = sanitize_text_field($_POST['user_delete_id']);
        $wp_user = get_wp_user_from_istep_user($id);
        if( $wp_user !== false ){
            wp_delete_user($wp_user->ID);
            echo '<div id="message" class="updated notice"><p>L\'utilisateur à été supprimé avec succès.</p></div>';
        }else{
            echo '<div class="notice notice-error"><p>Une erreur est survenue lors de la suppression</p></div>';
        }
    }
}

/**
 * Formulaire de modification d'un utilisateur
 * @return void
 */
function edit_user_form():void{
    if (isset($_POST['id']) || isset($_GET["id"])){
        $id_user = sanitize_text_field($_POST['id'] ?? $_GET["id"]);

        $teams = get_list_of_table(TABLE_TEAM_NAME);
        $users_teams = get_user_teams_id_by_user_id($id_user);

        $user_data = get_istep_user_by_id($id_user,"istep");
        $user_data_wp = get_user_by('id',$user_data->wp_user_id);

        $display_name = explode(" ",$user_data_wp->display_name);
        $last_name = $display_name[0] ?? "";
        $first_name = $display_name[1] ?? "";

        echo "<div class='update-user'>";
        echo "<h1>Modifier les informations d'un utilisateur</h1>";
        echo "<form method='POST' action=''>";
        echo '<div><label for="last_name" >Nom de famille : </label><input type="text" name="last_name" id="last_name" value="'.$last_name.'" required></div>';
        echo '<div><label for="first_name" >Prénom : </label><input type="text" name="first_name"  id="first_name" value="'.$first_name.'" required></div>';

        echo '<div><label for="phone" >Numéro de téléphone : </label><input type="tel" name="phone" id="phone" value="'.($user_data->nTelephone ?? "").'" required></div>';

        echo '<div><label for="office" >Bureau : </label><input type="text" name="office" id="office" value="'.($user_data->bureau ?? "").'" required></div>';
        echo '<div><label for="function" >Fonction : </label><input type="text" name="function" id="function" value="'.($user_data->fonction ?? "").'" required></div>';

        echo '<div><label for="tower" id="tower">Tour du bureau : <ul>';

        //Liste des tours
        $towerList = ["tour-46-00-2e","tour-46-00-3e","tour-46-00-4e","tour-46-45-2e","tour-56-66-5e","tour-56-55-5e"];
        foreach($towerList as $tower){
            echo '<li><label></label><input type="radio" name="tourBureau" value="'.$tower.'"'.($tower == $user_data->tourDuBureau ? "checked":""). ' />' .convert_tower_into_readable($tower).'</label> </li>';
        }
        echo ' </ul></label></div>';


        //Listes des équipes
        echo "<h2>Listes des équipes</h2>";
        echo "<table class=\"form-table\">";
        echo '<tr><th>Nom de l\'équipe</th><th>Fait partie de</th></tr>';
        foreach ($teams as $team){

            $team_name = $team->nom_equipe;
            $team_id = $team->id_equipe;

            //Coche toute les équipes dont l'utilisateur fait déjà partie
            $checked = (in_array($team_id,$users_teams))? "checked":"";

            echo '<tr><td>'.$team_name.'</td><td><input type="checkbox" name="teams[]" value="'.$team_id.'"'.$checked.' id="team-'.$team_id.'"></td></tr>';
        }
        echo "</table>";

        echo '<div><label for="rank" >Rang dans l\'équipe : </label><input type="text" id="rank" name="rank" value="'.($user_data->rangEquipe ?? "").'" required></div>';

        //Listes de campus
        echo '<div><label for="campus" >Campus : <select name="campus" id="campus">';
        $campus = get_list_of_table(TABLE_LOCATION_NAME);
        foreach ($campus as $one_campus){
            echo "<option value=\"".$one_campus->id_localisation."\">".$one_campus->nom_localisation."</option>";
        }
        echo '</select></label></div>';

        //Listes des employeur
        echo '<div><label for="employer" >Employeur : </label>
                    <select name="employer" id="employer">
                        <option value="sorbonne-universite">Sorbonne université</option>
                        <option value="cnrs">CNRS</option>
                        <option value="autre">Autre</option>
                    </select></div>';

        echo '<div><label for="mailCase" >Case de courrier : </label><input type="text" name="mailCase" id="mailCase" value="'.($user_data->caseCourrier ?? "").'" required></div>';


        echo '<input type="hidden" name="idUser" value="' . $id_user . '">';
        echo '<input type="hidden" name="idUserWp" value="' . $user_data->wp_user_id . '">';
        submit_button('Modifier','primary','changeTeams');
        echo "</form>";
        echo "</div>";
    }else{
        wp_redirect(admin_url("admin.php?page=istep_users_list"));

    }
}