<?php
// ---- Menu Administrateur ----
/**
 * Génère la page dans le panel administrateur
 * @return void
 */
function more_userdata_istep_menu(): void{
    add_menu_page(
        "Paramètre création d'utilisteur",
        "Roles de création d'utilisateur",
        "administrator",
        "istep_users_options",
        "more_userdata_istep_menu_content"
    );
    add_submenu_page(
        'istep_users_options', // slug du parent
        'Gérer les équipes', // titre de la page
        'Gérer les équipes', // titre du menu
        'manage_options', // capacité requise
        'istep_manage_teams', // slug de la page
        'more_userdata_istep_menu_team_page' // fonction de rappel
    );
    add_submenu_page(
        'admin.php?page=edit_teams&id=',
        'Modifier équipe',
        'Modifier équipe',
        'administrator',
        'edit_teams',
        'more_userdata_istep_edit_equipe_page'
    );
    add_submenu_page(
        'admin.php?page=delete_teams&id=',
        'Supprimer équipe',
        'Supprimer équipe',
        'administrator',
        'delete_teams',
        'more_userdata_istep_delete_equipe_page'
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
 * Sous menu qui gère l'ajout des différentes équipes
 * @return void
 */
function more_userdata_istep_menu_team_page() {
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
            global $wpdb;
            $table_name = TABLE_TEAM_NAME;
            $equipes = $wpdb->get_results("SELECT * FROM $table_name");
            foreach ($equipes as $equipe) {
                echo '<tr>';
                echo '<td>' . $equipe->id_equipe . '</td>';
                echo '<td>' . $equipe->nom_equipe . '</td>';
                echo '<td>
                        <form method="post" action="' . admin_url( 'admin.php?page=edit_teams&id=' . $equipe->id_equipe ) . '">
                            <input type="hidden" name="id" value="' . $equipe->id_equipe . '">
                            <button type="submit" class="button">Modifier</button>
                        </form>
                      </td>';
                echo '<td>
                        <form method="post" action="' . admin_url( 'admin.php?page=delete_teams&id=' . $equipe->id_equipe ) . '">
                            <input type="hidden" name="equipe_id_delete" value="' . $equipe->id_equipe . '">
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
    // Récupère l'ID de l'équipe à éditer depuis l'URL
    $id_equipe = $_GET['id'];

    // Vérifie si le formulaire a été soumis
    if (isset($_POST['submit']) && isset($id_equipe)) {
        if ( current_user_can( 'manage_options' ) ) {
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
    if ( current_user_can( 'manage_options' ) ) {
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