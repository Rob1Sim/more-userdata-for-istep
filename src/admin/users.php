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

    ?>
    <div class="wrap">
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
                <th>Equipe</th>
                <th>Fonction</th>
                <th>Email</th>
                <th>Numéro de téléphone</th>
                <th>Rang dans l'équipe</th>
                <th>Tour du bureau</th>
                <th>Bureau</th>
                <th>Campus</th>
                <th>Employeur</th>
                <th>Case courrier</th>
                <th>Supprimer</th>
            </tr>
            </thead>
            <tbody>
            <?php
            global $wpdb;
            $table_name = TABLE_TEAM_NAME;
            $users = get_list_of_table(TABLE_MEMBERS_NAME);
            foreach ($users as $user) {
                $wp_user = get_userdata( $user->wp_user_id );
                echo '<tr>';
                echo '<td>' . $user->id_membre . '</td>';
                echo '<td>' . $wp_user->display_name . '</td>';
                echo '<td>' . $wp_user->user_login . '</td>';
                echo '<td>
                        <form method="post" action="' . admin_url( 'admin.php?page=modify_users_teams&id=' . $user->id_membre ) . '">
                            <input type="hidden" name="id" value="' . $user->id_membre . '">
                            <button type="submit" class="button">Modifier</button>
                        </form>
                      </td>';
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
                        <form method="post" action="' . admin_url( 'admin.php?page=erase_user&id=' . $user->id_membre ) . '">
                            <input type="hidden" name="user_delete_id" value="' . $user->id_membre . '">
                            <button type="submit" class="button button-primary" style="background: #d0021b; border-color: #d0021b">Supprimer</button>
                        </form>
                      </td>';
                echo '</tr>';
            }
            ?>
            </tbody>
        </table>
    </div>
    <?php
}

/**
 * Formulaire de modification de l'équipe d'un utilisateur
 * @return void
 */
function more_userdata_istep_users_edit_teams():void
{
    if ( ! current_user_can( ADMIN_CAPACITY ) ) {
        wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
    }
    if ( isset($_POST['changeTeams']) && isset($_POST['idUser'])) {

        $id_user = $_POST['idUser'];
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
        if (isset($_POST['id'])){
            $id_user = sanitize_text_field($_POST['id']);
            $teams = get_list_of_table(TABLE_TEAM_NAME);

            $users_teams = get_user_teams_id_by_user_id($id_user);
            echo "<div>";
            echo "<h1>Modifier l'équipe d'un membre</h1>";
            echo "<form method='POST' action=''>";
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
            echo '<input type="hidden" name="idUser" value="' . $id_user . '">';
            submit_button('Modifier','primary','changeTeams');
            echo "</form>";
            echo "</div>";
        }else{
            wp_redirect(admin_url("admin.php?page=istep_users_list"));

        }
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