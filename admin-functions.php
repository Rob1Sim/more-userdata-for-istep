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
    add_submenu_page(
        'istep_users_options', // slug du parent
        'Gérer les équipes', // titre de la page
        'Gérer les équipes', // titre du menu
        ADMIN_CAPACITY, // capacité requise
        'istep_manage_teams', // slug de la page
        'more_userdata_istep_menu_team_page' // fonction de rappel
    );
    add_submenu_page(
        'istep_users_options',
        'Membres de l\'ISTeP',
        'Membres de l\'ISTeP',
        ADMIN_CAPACITY,
        'istep_users_list',
        'more_userdata_istep_users_list'
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
    add_submenu_page(
        'admin.php?page=edit_team_for_user',
        'Modifier l\'équipe d\'un membre',
        'Modifier l\'équipe d\'un membre',
        "read",
        'edit_member_team',
        'more_userdata_istep_edit_member_team_page'
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
                            echo '<label><input type="checkbox" name="istep_user_roles[]" value="'.$key.'" '
                                .checked(in_array($key, $selected_roles), true, false).'>'.$value['name'].'</label><br/>';
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

/**
 * Ajoute le formulaire de gestions des roles qui ont accès au panel administrateur
 * @return void
 */
function more_userdata_istep_menu_give_access(){
    if ( !can_user_access_this(get_option('admin_user_roles')) ) {
        wp_die( __( 'You do not have sufficient permissions to access this page.'.get_option('admin_user_roles')[0] ) );
    }
    if (isset($_POST['submitRoles'])) {
        // Met à jour les options avec les rôles sélectionnés
        if(isset($_POST['admin_user_roles'])){
            update_option('admin_user_roles', $_POST['admin_user_roles']);
            foreach (get_option('admin_user_roles') as $role) {
                $role_obj = get_role($role);
                $role_obj->add_cap(ADMIN_CAPACITY);
            }
            //si l'administrateur n'a plus les droits alors on lui redonne
            if(!in_array("administrator",get_option('admin_user_roles'))){
                $roles_already_stored = get_option('admin_user_roles');
                $roles_already_stored[] = "administrator";
                update_option('admin_user_roles', $roles_already_stored);
            }
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
    if ( current_user_can( ADMIN_CAPACITY ) ) {
        // Récupère l'ID de l'équipe à supprimer depuis l'URL
        $id_equipe = $_POST['equipe_id_delete'];

        // Supprime l'équipe de la base de données
        global $wpdb;
        $table_name = TABLE_TEAM_NAME;
        $wpdb->delete(
            $table_name,
            array(
                'id_equipe' => $id_equipe
            )
        );
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
    // Vérifie si le formulaire a été soumis
    if (isset($_POST['submit']) && isset($_POST["userID"])) {
        $id_member = $_POST["userID"];
        if (current_user_can(ADMIN_CAPACITY)) {
            // Met à jour les informations de l'équipe dans la base de données celon le membre
            foreach ($id_member as $user) {
                if (isset($user)) {
                    $team_number = $_POST['team-'.$user];
                    if (is_team_id_valid(intval($team_number))){
                        global $wpdb;
                        $wpdb->update(
                            TABLE_MEMBERS_NAME,
                            array(
                                'equipe' => $team_number
                            ),
                            array(
                                'id_membre' => $user
                            )
                        );
                    }
                    echo '<div id="message" class="updated notice"><p>Équipe modifiée avec succès.</p></div>';
                }
            }
        }

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
            $teams = $wpdb->get_results("SELECT * FROM $table_name");
            $users = get_list_of_table(TABLE_MEMBERS_NAME);
            foreach ($users as $user) {
                $wp_user = get_userdata( $user->wp_user_id );
                echo '<tr>';
                echo '<td>' . $user->id_membre . '</td>';
                echo '<td>' . $wp_user->display_name . '</td>';
                echo '<td>' . $wp_user->user_login . '</td>';
                echo '<td>';
                echo '<form method="post" action="">';
                echo '<input type="hidden" value="'.$user->id_membre.'" name="userID[]"/>';
                wp_nonce_field( 'modifier_equipe_membre_nonce', 'modifier_equipe_membre_nonce' );
                echo '<select name="team-'.$user->id_membre.'" id="team">';
                foreach ($teams as $team){
                    $teamName = $team->nom_equipe;
                    $teamId = $team->id_equipe;
                    if ($teamId == $user->equipe){
                        echo "<option value=\"".$teamId."\" selected>".$teamName."</option>";
                    }else{
                        echo "<option value=\"".$teamId."\">".$teamName."</option>";
                    }
                }
                echo '</select>';
                submit_button('Enregistrer', 'primary', 'submit', true);
                echo '</form></td>';

                echo '<td>' . $user->fonction . '</td>';
                echo '<td>' . $wp_user->user_email . '</td>';
                echo '<td>' . $user->nTelephone . '</td>';
                echo '<td>' . $user->rangEquipe . '</td>';
                echo '<td>' . convert_tower_into_readable($user->tourDuBureau) . '</td>';
                echo '<td>' . $user->bureau . '</td>';
                echo '<td>' . $user->campus . '</td>';
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