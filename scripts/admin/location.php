<?php
/**
 * Gestion du menu de sélections des campus
 */



wp_enqueue_script('more-userdata-for-istep-admin-js', plugins_url('../../public/scripts/more-userdata-for-istep-admin.js', __FILE__), array(), false, true);

/**
 * Gère la gestions des différents campus
 * @return void
 */
function more_userdata_istep_menu_location_page()
{
    if (!can_user_access_this(get_option('admin_user_roles'))) {
        wp_die(__('You do not have sufficient permissions to access this page.'));
    }
    // Vérifie si le formulaire a été soumis
    if (isset($_POST['submit'])) {
        // Ajoute une nouvelle équipe à la base de données
        $location_name = sanitize_text_field($_POST['nom_localisation']);
        if (isset($location_name) && $location_name !== "") {
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
            <?php wp_nonce_field('ajouter_campus_nonce', 'ajouter_campus_nonce'); ?>
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="nom_campus"><?php _e('Nom du campus:', 'istep_users'); ?></label></th>
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
                        <form method="post" action="' . admin_url('admin.php?page=edit_location&id=' . $location->id_localisation) . '">
                            <input type="hidden" name="id" value="' . $location->nom_localisation . '">
                            <button type="submit" class="button">Modifier</button>
                        </form>
                      </td>';
        echo '<td>
                        <form method="post" action="' . admin_url('admin.php?page=suppress_location&id=' . $location->id_localisation) . '">
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
function more_userdata_istep_edit_location_page()
{
    if (!can_user_access_this(get_option('admin_user_roles'))) {
        wp_die(__('You do not have sufficient permissions to access this page.'));
    }
    global $wpdb;

    $id_location = $_GET['id'];

    if (isset($_POST['submit']) && isset($id_location)) {
        if (current_user_can(ADMIN_CAPACITY)) {

            $location_name = sanitize_text_field($_POST['nom_location']);
            if (isset($location_name)) {
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
            <?php wp_nonce_field('modifier_location_nonce', 'modifier_location_nonce'); ?>
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="nom_equipe"><?php _e('Nom du campus:', 'istep_users'); ?></label></th>
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
function more_userdata_istep_delete_location_page()
{
    if (!can_user_access_this(get_option('admin_user_roles'))) {
        wp_die(__('You do not have sufficient permissions to access this page.'));
    }
    if (current_user_can(ADMIN_CAPACITY) && isset($_POST['location_id_delete'])) {

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
