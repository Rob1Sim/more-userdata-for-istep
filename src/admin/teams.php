<?php
/**
 * Gestion du menu de sélections des équipes
 */
namespace MUDF_ISTEP\Admin;

use function MUDF_ISTEP\can_user_access_this;
use function MUDF_ISTEP\get_list_of_table;
use const MUDF_ISTEP\ADMIN_CAPACITY;

wp_enqueue_script('more-userdata-for-istep-admin-js', plugins_url('../../scripts/more-userdata-for-istep-admin.js', __FILE__), array(), false, true);

/**
 * Sous menu qui gère l'ajout des différentes équipes
 * @return void
 */
function more_userdata_istep_menu_team_page(): void
{
    if (!can_user_access_this(get_option('admin_user_roles'))) {
        wp_die(__('You do not have sufficient permissions to access this page.'));
    }
    // Vérifie si le formulaire a été soumis
    if (isset($_POST['submit'])) {
        // Ajoute une nouvelle équipe à la base de données
        $nom_equipe = sanitize_text_field($_POST['nom_equipe']);
        if (isset($nom_equipe) && $nom_equipe !== "") {
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
            <?php wp_nonce_field('ajouter_equipe_nonce', 'ajouter_equipe_nonce'); ?>
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="nom_equipe"><?php _e('Nom de l\'équipe:', 'istep_users'); ?></label></th>
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
                        <form method="post" action="' . admin_url('admin.php?page=edit_teams&id=' . $team->id_equipe) . '">
                            <input type="hidden" name="id" value="' . $team->id_equipe . '">
                            <button type="submit" class="button">Modifier</button>
                        </form>
                      </td>';
        echo '<td>
                        <form method="post" action="' . admin_url('admin.php?page=delete_teams&id=' . $team->id_equipe) . '">
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
function more_userdata_istep_edit_equipe_page(): void
{
    if (!can_user_access_this(get_option('admin_user_roles'))) {
        wp_die(__('You do not have sufficient permissions to access this page.'));
    }
    // Récupère l'ID de l'équipe à éditer depuis l'URL
    $id_equipe = $_GET['id'];

    // Vérifie si le formulaire a été soumis
    if (isset($_POST['submit']) && isset($id_equipe)) {
        if (current_user_can(ADMIN_CAPACITY)) {
            // Met à jour les informations de l'équipe dans la base de données
            $nom_equipe = sanitize_text_field($_POST['nom_equipe']);
            if (isset($nom_equipe)) {
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
            <?php wp_nonce_field('modifier_equipe_nonce', 'modifier_equipe_nonce'); ?>
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="nom_equipe"><?php _e('Nom de l\'équipe:', 'istep_users'); ?></label></th>
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
function more_userdata_istep_delete_equipe_page()
{
    if (!can_user_access_this(get_option('admin_user_roles'))) {
        wp_die(__('You do not have sufficient permissions to access this page.'));
    }
    if (current_user_can(ADMIN_CAPACITY) && isset($_POST['equipe_id_delete'])) {
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
