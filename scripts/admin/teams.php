<?php
/**
 * Gestion du menu de sélections des équipes
 */


use MUDF_ISTEP\Entity\Team;
use MUDF_ISTEP\Exception\EntityNotFound;
use MUDF_ISTEP\Exception\TeamNotFound;

wp_enqueue_script('more-userdata-for-istep-admin-js', plugins_url('../../public/scripts/more-userdata-for-istep-admin.js', __FILE__), array(), false, true);

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
            $team = new Team($nom_equipe);
            $team->save();
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
            $teams = Team::getAll();
    foreach ($teams as $team) {
        echo '<tr>';
        echo '<td>' . $team->getId() . '</td>';
        echo '<td>' . $team->getName() . '</td>';
        echo '<td>
                        <form method="post" action="' . admin_url('admin.php?page=edit_teams&id=' . $team->getId()) . '">
                            <input type="hidden" name="id" value="' . $team->getId() . '">
                            <button type="submit" class="button">Modifier</button>
                        </form>
                      </td>';
        echo '<td>
                        <form method="post" action="' . admin_url('admin.php?page=delete_teams&id=' . $team->getId()) . '">
                            <input type="hidden" name="equipe_id_delete" value="' . $team->getId() . '">
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
    $id_equipe = sanitize_text_field($_GET['id']);
    if (isset($id_equipe)){
        try {
            $team = Team::findById($id_equipe);
            // Vérifie si le formulaire a été soumis
            if (isset($_POST['submit'])) {
                if (current_user_can(ADMIN_CAPACITY)) {
                    // Met à jour les informations de l'équipe dans la base de données
                    $nom_equipe = sanitize_text_field($_POST['nom_equipe']);
                    if (isset($nom_equipe)) {
                        $team->setName($nom_equipe);
                        $team->save();
                        echo '<div id="message" class="updated notice"><p>Équipe modifier avec succès.</p></div>';
                    }
                } else {
                    echo '<div id="message" class="notice notice-error"><p>Vous n\'avez pas la permission de faire ça.</p></div>';
                }
            }

            // Récupère les informations de l'équipe depuis la base de données

            ?>
            <div class="wrap">
                <h1>Modifier l'équipe <?php echo $team->getName(); ?></h1>
                <form method="post" action="">
                    <?php wp_nonce_field('modifier_equipe_nonce', 'modifier_equipe_nonce'); ?>
                    <table class="form-table">
                        <tr>
                            <th scope="row"><label for="nom_equipe"><?php _e('Nom de l\'équipe:', 'istep_users'); ?></label></th>
                            <td>
                                <input type="text" name="nom_equipe" id="nom_equipe" value="<?php echo $team->getName(); ?>">
                            </td>
                        </tr>
                    </table>
                    <?php submit_button('Enregistrer', 'primary', 'submit', true); ?>
                </form>
            </div>
            <?php
        } catch (EntityNotFound|TeamNotFound $e) {
            echo '<div id="message" class="notice notice-error"><p>Une erreur est survenue.</p></div>';
        }
    }else{
        echo '<div id="message" class="notice notice-error"><p>Une erreur est survenue.</p></div>';
    }

}

/**
 * Supprime de la bd l'équipe avec l'id correspondant
 * @return void
 */
function more_userdata_istep_delete_equipe_page(): void
{
    if (!can_user_access_this(get_option('admin_user_roles'))) {
        wp_die(__('You do not have sufficient permissions to access this page.'));
    }
    if (current_user_can(ADMIN_CAPACITY) && isset($_POST['equipe_id_delete'])) {
        // Récupère l'ID de l'équipe à supprimer depuis l'URL
        $id_equipe = sanitize_text_field($_POST['equipe_id_delete']);
        // Supprime l'équipe de la base de données
        try {
            $team_to_delete = Team::findById($id_equipe);
            $team_to_delete->delete();
            // Vérifie s'il reste des équipes dans la table
            if (count(Team::getAll()) == 0) {
                // Crée l'équipe "Pas d'équipe"
                $new_team = new Team("Pas d'équipe");
                $new_team->save();
            }

            echo '<div id="message" class="updated notice"><p>Équipe supprimée avec succès.</p></div>';
        } catch (EntityNotFound|TeamNotFound $e) {
            echo '<div id="message" class="notice notice-error"><p>Une erreur est survenue lors de la suppression.</p></div>';
        }
    } else {
        echo '<div id="message" class="notice notice-error"><p>Vous n\'avez pas la permission de faire ça.</p></div>';

    }

}
