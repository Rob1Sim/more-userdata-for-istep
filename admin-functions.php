<?php
// ---- Menu Administrateur ----
wp_enqueue_script('more-userdata-for-istep-admin-js',plugins_url('scripts/more-userdata-for-istep-admin.js',__FILE__),array(), false, true);


/**
 * Génère la page dans le panel administrateur
 * @return void
 */
function more_userdata_istep_menu(): void{
    add_menu_page(
        "Paramètre création d'utilisteur",
        "Membres de l'ISTeP paramètres",
        ADMIN_CAPACITY,
        "istep_users_options",
        "more_userdata_istep_menu_content"
    );
    add_submenu_page(
        'istep_users_options', // slug du parent
        'Gérer les permissions', // titre de la page
        'Gérer les permissions', // titre du menu
        'administrator', // capacité requise
        'admin_users_options', // slug de la page
        'more_userdata_istep_menu_give_access' // fonction de rappel
    );
    // --- Les équipes ---
    add_submenu_page(
        'istep_users_options', // slug du parent
        'Gérer les équipes', // titre de la page
        'Gérer les équipes', // titre du menu
        ADMIN_CAPACITY, // capacité requise
        'istep_manage_teams', // slug de la page
        'more_userdata_istep_menu_team_page' // fonction de rappel
    );
    add_submenu_page(
        'admin.php?page=edit_teams&id=',
        'Modifier équipe',
        'Modifier équipe',
        ADMIN_CAPACITY,
        'edit_teams',
        'more_userdata_istep_edit_equipe_page'
    );
    add_submenu_page(
        'admin.php?page=delete_teams&id=',
        'Supprimer équipe',
        'Supprimer équipe',
        ADMIN_CAPACITY,
        'delete_teams',
        'more_userdata_istep_delete_equipe_page'
    );
    // --- Les campus ---
    add_submenu_page(
        'istep_users_options', // slug du parent
        'Gérer les campus', // titre de la page
        'Gérer les campus', // titre du menu
        ADMIN_CAPACITY, // capacité requise
        'istep_manage_location', // slug de la page
        'more_userdata_istep_menu_location_page' // fonction de rappel
    );
    add_submenu_page(
        'admin.php?page=edit_location&id=',
        'Modifier le campus',
        'Modifier le campus',
        ADMIN_CAPACITY,
        'edit_location',
        'more_userdata_istep_edit_location_page'
    );
    add_submenu_page(
        'admin.php?page=suppress_location&id=',
        'Supprimer campus',
        'Supprimer campus',
        ADMIN_CAPACITY,
        'suppress_location',
        'more_userdata_istep_delete_location_page'
    );
    // --- Les utilisateurs ---
    add_submenu_page(
        'istep_users_options',
        'Membres de l\'ISTeP',
        'Membres de l\'ISTeP',
        ADMIN_CAPACITY,
        'istep_users_list',
        'more_userdata_istep_users_list'
    );
    add_submenu_page(
        'admin.php?page=modify_users_teams&id=',
        'Modifier équipe',
        'Modifier équipe',
        ADMIN_CAPACITY,
        'modify_users_teams',
        'more_userdata_istep_users_edit_teams'
    );
}
add_action( 'admin_menu', 'more_userdata_istep_menu' );

/**
 * Gère le contenue de la page administrateur
 * @return void
 */
function more_userdata_istep_menu_content(): void {
    if ( !can_user_access_this(get_option('admin_user_roles')) ) {
        wp_die( __( 'You do not have sufficient permissions to access this page.'.get_option('admin_user_roles')[0] ) );
    }

    // Formulaire de la selctions des rôles
    if (isset($_POST['submit']) && isset($_POST['istep_user_roles'])) {
        // Met à jour les options avec les rôles sélectionnés
        if (gettype($_POST['istep_user_roles']) == "string"){
            update_option('istep_user_roles', [$_POST['istep_user_roles']]);
        } else {
            update_option('istep_user_roles', $_POST['istep_user_roles']);
        }

        set_rights_to_administrator('istep_user_roles');
        echo '<div id="message" class="updated notice"><p>Rôles mis à jour avec succès.</p></div>';
    }
    // Formulaire du rôle par défaut
    if(isset($_POST["submit-default-role"])){
        if (isset($_POST["default-role"])
            && gettype($_POST["default-role"]) == "string"){

            update_option('default_role',sanitize_text_field($_POST["default-role"]));

            echo '<div id="message" class="updated notice"><p>Le rôle par défaut a été mis à jour avec succès. </p></div>';
        }
    }
    // Formulaire du lien par défaut
    if(isset($_POST["submit-redirect-link"])){
        if (isset($_POST["redirect-link-default"])
            && gettype($_POST["redirect-link-default"]) == "string") {

            update_option('default_redirect_link',sanitize_text_field($_POST["redirect-link-default"]));

            echo '<div id="message" class="updated notice"><p>Le lien par défaut a été mis à jour avec succès. </p></div>';
        }
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
                            if ($key == "administrator") {
                                echo '<label><input type="checkbox" name="istep_user_roles[]" value="' . $key . '" checked disabled>' . $value['name'] . '</label><br/>';
                            }else{
                                echo '<label><input type="checkbox" name="istep_user_roles[]" value="'.$key.'" '
                                    .checked(in_array($key, $selected_roles), true, false).'>'.$value['name'].'</label><br/>';
                            }
                        }
                        ?>
                    </td>
                </tr>
            </table>
            <?php submit_button('Enregistrer les rôles', 'primary', 'submit', true); ?>
        </form>
        <form method="post" action="">
            <h2>Rôle par défaut</h2>
            <p>Représente le rôle attribué par défaut à la création de l'utilisateur via l'utilisation du formulaire.</p>
            <label for="default-role">Rôle</label>
            <?php
            //Choix du rôle selectionné de base
            $roles = get_editable_roles();
            echo '<select name="default-role" id="default-role">';
            foreach ($roles as $key => $value){
                if ($key == get_option("default_role")){
                    echo "<option value=\"".$key."\" selected>".$value['name']."</option>";
                }else{
                    echo "<option value=\"".$key."\">".$value['name']."</option>";
                }
            }
            echo '</select>';
            ?>
            <?php submit_button('Enregistrer', 'primary', 'submit-default-role', true); ?>
        </form>
        <form method="post" action="">
            <h2>Lien de redirection par défaut</h2>
            <p>Représente le lien vers lequel vous êtes rediriger après avoir envoyer le formulaire:
             le mieux est de prendre le lien de la page où se situe le formulaire</p>

            <label for="redirect-link-default">Le slug de la page(<i>sur cette page le slug est sample-page : http://localhost:10004/sample-page/</i>)</label>
            <input type="text" name="redirect-link-default" id="redirect-link-default" value="<?php echo get_option("default_redirect_link")?>">

            <?php submit_button('Enregistrer', 'primary', 'submit-redirect-link', true); ?>
        </form>
    </div>
    <?php
}

/**
 * Ajoute le formulaire de gestions des roles qui ont accès au panel administrateur
 * @return void
 */
function more_userdata_istep_menu_give_access(){
    if ( !can_user_access_this(get_option('admin_user_roles')) ) {
        wp_die( __( 'You do not have sufficient permissions to access this page.'.get_option('admin_user_roles')[0] ) );
    }
    if (isset($_POST['submitRoles'])&& isset($_POST['admin_user_roles'])) {
        // Met à jour les options avec les rôles sélectionnés
        if(isset($_POST['admin_user_roles'])){
            update_option('admin_user_roles', $_POST['admin_user_roles']);
            foreach (get_option('admin_user_roles') as $role) {
                $role_obj = get_role($role);
                $role_obj->add_cap(ADMIN_CAPACITY);
            }
            set_rights_to_administrator('admin_user_roles');
            update_option('admin_user_roles',delete_cap_if_no_need_anymore(ADMIN_CAPACITY,get_option('admin_user_roles')));
        }

        echo '<div id="message" class="updated notice"><p>Rôles mis à jour avec succès.</p></div>';
    }
    ?>
    <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
    <form method="post" action="">
        <?php wp_nonce_field( 'admin_user_roles_nonce', 'admin_user_roles_nonce' ); ?>
        <table class="form-table">
            <tr>
                <th scope="row"><label for="admin_user_roles"><?php _e( 'Rôles qui peuvent accéder au menu d\'administration:', 'istep_users' ); ?></label></th>
                <td>
                    <?php
                    // Récupère tous les rôles WordPress
                    $roles = get_editable_roles();

                    // Récupère les rôles sélectionnés dans la base de données
                    $selected_roles = get_option('admin_user_roles', array());
                    // Affiche une checkbox pour chaque rôle
                    foreach ($roles as $key => $value) {
                        if ($key == "administrator"){
                            echo '<label><input type="checkbox" name="admin_user_roles[]" value="'.$key.'" checked disabled>'.$value['name'].'</label><br/>';
                        } else {
                            echo '<label><input type="checkbox" name="admin_user_roles[]" value="'.$key.'" '
                                .checked(in_array($key, $selected_roles), true, false).'>'.$value['name'].'</label><br/>';
                        }

                    }
                    ?>
                </td>
            </tr>
        </table>
        <?php submit_button('Enregistrer les rôles', 'primary', 'submitRoles', true); ?>
    </form>
    <?php
}

/**
 * Sous menu qui gère l'ajout des différentes équipes
 * @return void
 */
function more_userdata_istep_menu_team_page() {
    if ( !can_user_access_this(get_option('admin_user_roles')) ) {
        wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
    }
    // Vérifie si le formulaire a été soumis
    if (isset($_POST['submit'])) {
        // Ajoute une nouvelle équipe à la base de données
        $nom_equipe = sanitize_text_field($_POST['nom_equipe']);
        if (isset($nom_equipe) && $nom_equipe !== ""){
            global $wpdb;
            $wpdb->insert(
                TABLE_TEAM_NAME,
                array(
                    'nom_equipe' => $nom_equipe
                )
            );
            echo '<div id="message" class="updated notice"><p>Équipe ajoutée avec succès.</p></div>';
        }
    }
    ?>
    <div class="wrap">
        <h1></h1>
        <h2>Ajouter une nouvelle équipe</h2>
        <form method="post" action="">
            <?php wp_nonce_field( 'ajouter_equipe_nonce', 'ajouter_equipe_nonce' ); ?>
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="nom_equipe"><?php _e( 'Nom de l\'équipe:', 'istep_users' ); ?></label></th>
                    <td>
                        <input type="text" name="nom_equipe" id="nom_equipe" value="" required>
                    </td>
                </tr>
            </table>
            <?php submit_button('Ajouter', 'primary', 'submit', true); ?>
        </form>
        <hr>
        <h2>Liste des équipes</h2>
        <table class="wp-list-table widefat fixed striped">
            <thead>
            <tr>
                <th>ID</th>
                <th>Nom de l'équipe</th>
            </tr>
            </thead>
            <tbody>
            <?php
            $teams = get_list_of_table(TABLE_TEAM_NAME);
            foreach ($teams as $team) {
                echo '<tr>';
                echo '<td>' . $team->id_equipe . '</td>';
                echo '<td>' . $team->nom_equipe . '</td>';
                echo '<td>
                        <form method="post" action="' . admin_url( 'admin.php?page=edit_teams&id=' . $team->id_equipe ) . '">
                            <input type="hidden" name="id" value="' . $team->id_equipe . '">
                            <button type="submit" class="button">Modifier</button>
                        </form>
                      </td>';
                echo '<td>
                        <form method="post" action="' . admin_url( 'admin.php?page=delete_teams&id=' . $team->id_equipe ) . '">
                            <input type="hidden" name="equipe_id_delete" value="' . $team->id_equipe . '">
                            <button type="submit" class="button">Supprimer</button>
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
 * Modifie les informations d'une équipe
 * @return void
 */
function more_userdata_istep_edit_equipe_page() {
    if ( !can_user_access_this(get_option('admin_user_roles')) ) {
        wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
    }
    // Récupère l'ID de l'équipe à éditer depuis l'URL
    $id_equipe = $_GET['id'];

    // Vérifie si le formulaire a été soumis
    if (isset($_POST['submit']) && isset($id_equipe)) {
        if ( current_user_can( ADMIN_CAPACITY ) ) {
            // Met à jour les informations de l'équipe dans la base de données
            $nom_equipe = sanitize_text_field($_POST['nom_equipe']);
            if (isset($nom_equipe)){
                global $wpdb;
                $wpdb->update(
                    TABLE_TEAM_NAME,
                    array(
                        'nom_equipe' => $nom_equipe
                    ),
                    array(
                        'id_equipe' => $id_equipe
                    )
                );
                echo '<div id="message" class="updated notice"><p>Équipe modifiée avec succès.</p></div>';
            }
        } else {
            echo '<div id="message" class="notice notice-error"><p>Vous n\'avez pas la permission de faire ça.</p></div>';
        }
    }

    // Récupère les informations de l'équipe depuis la base de données
    global $wpdb;
    $table_name = TABLE_TEAM_NAME;
    $equipe = $wpdb->get_row("SELECT * FROM $table_name WHERE id_equipe = $id_equipe");

    ?>
    <div class="wrap">
        <h1>Modifier l'équipe <?php echo $equipe->nom_equipe; ?></h1>
        <form method="post" action="">
            <?php wp_nonce_field( 'modifier_equipe_nonce', 'modifier_equipe_nonce' ); ?>
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="nom_equipe"><?php _e( 'Nom de l\'équipe:', 'istep_users' ); ?></label></th>
                    <td>
                        <input type="text" name="nom_equipe" id="nom_equipe" value="<?php echo $equipe->nom_equipe; ?>">
                    </td>
                </tr>
            </table>
            <?php submit_button('Enregistrer', 'primary', 'submit', true); ?>
        </form>
    </div>
    <?php
}

/**
 * Supprime de la bd l'équipe avec l'id correspondant
 * @return void
 */
function more_userdata_istep_delete_equipe_page() {
    if ( !can_user_access_this(get_option('admin_user_roles')) ) {
        wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
    }
    if ( current_user_can( ADMIN_CAPACITY ) && isset($_POST['equipe_id_delete']) ) {
        // Récupère l'ID de l'équipe à supprimer depuis l'URL
        $id_equipe = sanitize_text_field($_POST['equipe_id_delete']);
        // Supprime l'équipe de la base de données
        global $wpdb;
        $table_name = TABLE_TEAM_NAME;
        $wpdb->delete(
            $table_name,
            array(
                'id_equipe' => intval($id_equipe)
            )
        );
        // Vérifie s'il reste des équipes dans la table
        $count = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
        if ($count == 0) {
            // Crée l'équipe "Pas d'équipe"
            $wpdb->insert(
                $table_name,
                array(
                    'id_equipe' => 1,
                    'nom_equipe' => 'Pas d\'équipe',
                )
            );
        }

        echo '<div id="message" class="updated notice"><p>Équipe supprimée avec succès.</p></div>';
    } else {
        echo '<div id="message" class="notice notice-error"><p>Vous n\'avez pas la permission de faire ça.</p></div>';

    }

}

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
 * Gère la gestions des différents campus
 * @return void
 */
function more_userdata_istep_menu_location_page(){
    if ( !can_user_access_this(get_option('admin_user_roles')) ) {
        wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
    }
    // Vérifie si le formulaire a été soumis
    if (isset($_POST['submit'])) {
        // Ajoute une nouvelle équipe à la base de données
        $location_name = sanitize_text_field($_POST['nom_localisation']);
        if (isset($location_name) && $location_name !== ""){
            global $wpdb;
            $wpdb->insert(
                TABLE_LOCATION_NAME,
                array(
                    'nom_localisation' => $location_name
                )
            );
            echo '<div id="message" class="updated notice"><p>Campus ajoutée avec succès.</p></div>';
        }
    }
    ?>
    <div class="wrap">
        <h1></h1>
        <h2>Ajouter un nouveau campus</h2>
        <form method="post" action="">
            <?php wp_nonce_field( 'ajouter_campus_nonce', 'ajouter_campus_nonce' ); ?>
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="nom_campus"><?php _e( 'Nom du campus:', 'istep_users' ); ?></label></th>
                    <td>
                        <input type="text" name="nom_localisation" id="nom_campus" value="" required>
                    </td>
                </tr>
            </table>
            <?php submit_button('Ajouter', 'primary', 'submit', true); ?>
        </form>
        <hr>
        <h2>Liste des campus</h2>
        <table class="wp-list-table widefat fixed striped">
            <thead>
            <tr>
                <th>ID</th>
                <th>Nom du campus</th>
            </tr>
            </thead>
            <tbody>
            <?php
            $locations = get_list_of_table(TABLE_LOCATION_NAME);
            foreach ($locations as $location) {
                echo '<tr>';
                echo '<td>' . $location->id_localisation . '</td>';
                echo '<td>' . $location->nom_localisation . '</td>';
                echo '<td>
                        <form method="post" action="' . admin_url( 'admin.php?page=edit_location&id=' . $location->id_localisation ) . '">
                            <input type="hidden" name="id" value="' . $location->nom_localisation . '">
                            <button type="submit" class="button">Modifier</button>
                        </form>
                      </td>';
                echo '<td>
                        <form method="post" action="' . admin_url( 'admin.php?page=suppress_location&id=' . $location->id_localisation ) . '">
                            <input type="hidden" name="location_id_delete" value="' . $location->id_localisation . '">
                            <button type="submit" class="button">Supprimer</button>
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
 * Modifie les informations d'un campus
 * @return void
 */
function more_userdata_istep_edit_location_page() {
    if ( !can_user_access_this(get_option('admin_user_roles')) ) {
        wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
    }
    global $wpdb;

    $id_location = $_GET['id'];

    if (isset($_POST['submit']) && isset($id_location)) {
        if ( current_user_can( ADMIN_CAPACITY ) ) {

            $location_name = sanitize_text_field($_POST['nom_location']);
            if (isset($location_name)){
                $wpdb->update(
                    TABLE_LOCATION_NAME,
                    array(
                        'nom_localisation' => $location_name
                    ),
                    array(
                        'id_localisation' => $id_location
                    )
                );
                echo '<div id="message" class="updated notice"><p>Campus modifiée avec succès.</p></div>';
            }
        } else {
            echo '<div id="message" class="notice notice-error"><p>Vous n\'avez pas la permission de faire ça.</p></div>';
        }
    }


    $table_name = TABLE_LOCATION_NAME;
    $location = $wpdb->get_row("SELECT * FROM $table_name WHERE id_localisation = $id_location");

    ?>
    <div class="wrap">
        <h1>Modifier le campus <?php echo $location->nom_localisation; ?></h1>
        <form method="post" action="">
            <?php wp_nonce_field( 'modifier_location_nonce', 'modifier_location_nonce' ); ?>
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="nom_equipe"><?php _e( 'Nom du campus:', 'istep_users' ); ?></label></th>
                    <td>
                        <input type="text" name="nom_location" id="nom_equipe" value="<?php echo $location->nom_localisation; ?>">
                    </td>
                </tr>
            </table>
            <?php submit_button('Enregistrer', 'primary', 'submit', true); ?>
        </form>
    </div>
    <?php
}

/**
 * Supprime de la bd le campus avec l'id correspondant
 * @return void
 */
function more_userdata_istep_delete_location_page() {
    if ( !can_user_access_this(get_option('admin_user_roles')) ) {
        wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
    }
    if ( current_user_can( ADMIN_CAPACITY ) && isset($_POST['location_id_delete']) ) {

        $id_location = sanitize_text_field($_POST['location_id_delete']);

        global $wpdb;
        $table_name = TABLE_LOCATION_NAME;
        $wpdb->delete(
            $table_name,
            array(
                'id_localisation' => intval($id_location)
            )
        );
        // Vérifie s'il reste des équipes dans la table
        $count = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
        if ($count == 0) {

            $wpdb->insert(
                $table_name,
                array(
                    'id_localisation' => 1,
                    'nom_localisation' => 'Sorbonne Université - Campus Pierre et Marie Curie',
                )
            );
        }

        echo '<div id="message" class="updated notice"><p>Campus supprimée avec succès.</p></div>';
    } else {
        echo '<div id="message" class="notice notice-error"><p>Vous n\'avez pas la permission de faire ça.</p></div>';

    }

}
/**
 * Ajoute a l'option passé en paramètre l'administrateur
 * @param string $option_name
 * @return void
 */
function set_rights_to_administrator(string $option_name){
    //si l'administrateur n'a plus les droits alors on lui redonne
    if(!in_array("administrator",get_option($option_name))){
        $roles_already_stored = get_option($option_name);
        $roles_already_stored[] = "administrator";
        update_option($option_name, $roles_already_stored);
    }
}