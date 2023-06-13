<?php
/**
 * Gestion du menu de sélections des campus
 */


use MUDF_ISTEP\Entity\Location;
use MUDF_ISTEP\Exception\EntityNotFound;

wp_enqueue_script('more-userdata-for-istep-admin-js', plugins_url('../../public/scripts/more-userdata-for-istep-admin.js', __FILE__), array(), false, true);

/**
 * Gère la gestions des différents campus
 * @return void
 */
function more_userdata_istep_menu_location_page(): void
{
    if (!can_user_access_this(get_option('admin_user_roles'))) {
        wp_die(__('You do not have sufficient permissions to access this page.'));
    }
    // Vérifie si le formulaire a été soumis
    if (isset($_POST['submit'])) {
        // Ajoute une nouvelle équipe à la base de données
        $location_name = sanitize_text_field($_POST['nom_localisation']);
        if (isset($location_name) && $location_name !== "") {
            $new_location = new Location($location_name);
            $new_location->save();
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
            $locations = Location::getAll();
    foreach ($locations as $location) {
        echo '<tr>';
        echo '<td>' . $location->getId() . '</td>';
        echo '<td>' . $location->getName() . '</td>';
        echo '<td>
                        <form method="post" action="' . admin_url('admin.php?page=edit_location&id=' . $location->getId()) . '">
                            <input type="hidden" name="id" value="' . $location->getName() . '">
                            <button type="submit" class="button">Modifier</button>
                        </form>
                      </td>';
        echo '<td>
                        <form method="post" action="' . admin_url('admin.php?page=suppress_location&id=' . $location->getId()) . '">
                            <input type="hidden" name="location_id_delete" value="' . $location->getId() . '">
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
 * Modifie les informations d'un campus
 * @return void
 * @throws EntityNotFound
 */
function more_userdata_istep_edit_location_page(): void
{
    if (!can_user_access_this(get_option('admin_user_roles'))) {
        wp_die(__('You do not have sufficient permissions to access this page.'));
    }

    $id_location = $_GET['id'];
    if (isset($id_location)){
        $location_object = Location::findById($id_location);
        if (isset($_POST['submit'])) {
            if (current_user_can(ADMIN_CAPACITY)) {

                $location_name = sanitize_text_field($_POST['nom_location']);
                if (isset($location_name)) {

                    $location_object->setName($location_name);
                    $location_object->save();
                    echo '<div id="message" class="updated notice"><p>Campus modifiée avec succès.</p></div>';
                    echo '<a href="'.admin_url("admin.php?page=istep_manage_location").'">Retour à la liste</a>';
                }
            } else {
                echo '<div id="message" class="notice notice-error"><p>Vous n\'avez pas la permission de faire ça.</p></div>';
            }
        }

        ?>
        <div class="wrap">
            <h1>Modifier le campus <?php echo $location_object->getName(); ?></h1>
            <form method="post" action="">
                <?php wp_nonce_field('modifier_location_nonce', 'modifier_location_nonce'); ?>
                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="nom_equipe"><?php _e('Nom du campus:', 'istep_users'); ?></label></th>
                        <td>
                            <input type="text" name="nom_location" id="nom_equipe" value="<?php echo $location_object->getName(); ?>">
                        </td>
                    </tr>
                </table>
                <?php submit_button('Enregistrer', 'primary', 'submit', true); ?>
            </form>
        </div>
        <?php
    }else{
        ?>
        <div class="notice notice-error"><p>Une erreur est survenue</p></div>
        <?php
    }

}

/**
 * Supprime de la bd le campus avec l'id correspondant
 * @return void
 * @throws EntityNotFound
 */
function more_userdata_istep_delete_location_page(): void
{
    if (!can_user_access_this(get_option('admin_user_roles'))) {
        wp_die(__('You do not have sufficient permissions to access this page.'));
    }
    if (current_user_can(ADMIN_CAPACITY) && isset($_POST['location_id_delete'])) {

        $id_location = sanitize_text_field($_POST['location_id_delete']);
        $location = Location::findById($id_location);
        $delete = $location->delete();
        if (!$delete) {
            echo '<div id="message" class="notice notice-error"><p>Impossible de supprimer la localisation par défaut.</p></div>';
            echo '<a href="'.admin_url("admin.php?page=istep_manage_location").'">Retour à la liste</a>';
            exit();
        }

        if (count(Location::getAll()) == 0) {
            $new_location = new Location("Sorbonne Université - Campus Pierre et Marie Curie");
            $new_location->save();
        }

        echo '<div id="message" class="updated notice"><p>Campus supprimée avec succès.</p></div>';
        echo '<a href="'.admin_url("admin.php?page=istep_manage_location").'">Retour à la liste</a>';
    } else {
        echo '<div id="message" class="notice notice-error"><p>Vous n\'avez pas la permission de faire ça.</p></div>';

    }

}
