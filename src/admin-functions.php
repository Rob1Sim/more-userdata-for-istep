<?php
/**
 * Gestion de la partie More User data for ISTeP dans le panel administrateur
 */
// ---- Menu Administrateur ----
wp_enqueue_script('more-userdata-for-istep-admin-js',plugins_url('../scripts/more-userdata-for-istep-admin.js',__FILE__),array(), false, true);
wp_enqueue_style('more-userdata-for-istep-admin',plugins_url('../styles/more-userdata-for-istep-admin.css',__FILE__));

require_once( plugin_dir_path( __FILE__ ) . 'admin/location.php' );
require_once( plugin_dir_path( __FILE__ ) . 'admin/users.php' );
require_once( plugin_dir_path( __FILE__ ) . 'admin/teams.php' );


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
        'admin.php?page=modify_users&id=',
        'Modifier l\'utilisateur',
        'Modifier l\'utilisateur',
        ADMIN_CAPACITY,
        'modify_users_data',
        'more_userdata_istep_users_edit_data'
    );
    add_submenu_page(
        'admin.php?page=erase_user&id=',
        'Supprimer un utilisateur',
        'Supprimer un utilisateur',
        ADMIN_CAPACITY,
        'erase_user',
        'more_userdata_istep_users_delete_user'
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

