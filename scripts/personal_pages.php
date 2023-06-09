<?php
/**
 * Gestion des pages personnels
 */
wp_enqueue_style('more-userdata-for-istep', plugins_url('../public/styles/more-userdata-for-istep.css', __FILE__));
wp_enqueue_script('more-userdata-for-istep-js', plugins_url('../public/scripts/more-userdata-for-istep.js', __FILE__), array(), false, true);

use MUDF_ISTEP\Entity\Member;
use MUDF_ISTEP\Entity\PersonalPage;
use MUDF_ISTEP\Exception\EntityNotFound;
use MUDF_ISTEP\Exception\InvalidParameter;
use MUDF_ISTEP\Exception\MemberNotFound;
use MUDF_ISTEP\Exception\UpdateError;

add_shortcode('istep_user_data', 'display_users_data');
/**
 * Affiche diverses informations de l'utilisateur sur la page de base
 * @return string
 */
function display_users_data(): string
{
    $current_page_slug = get_post_field('post_name', get_queried_object_id());
    $page_author_info = get_user_by('login', $current_page_slug);

    try {
        $userData = Member::findById($page_author_info->ID, "wp");
        $userAvatar = $userData->getAvatar();
        $userTower = $userData->getReadableOfficeTower();
        $userTeams = $userData->getTeamsNames();
        $userFunctions = $userData->getFunction();
        $userPhone = $userData->getPhone();
        $userLocation = $userData->getLocation();
        $userOffice = $userData->getOffice();

        $html = <<<HTML
    <div class="user-info-container">
        <div>
            $userAvatar
        </div>
        <div class="user-info-text-container">
            <div>
                <h5>Fonction</h5>
                <p>$userFunctions</p>
                
                <h5>Equipes</h5>
HTML;
        foreach ($userTeams as $userTeam) {
            $html .= "<p>$userTeam</p>";
        }
        $html .= <<<HTML
            </div>
            <div>
                <h5>Coordonées :</h5>
                <p><strong>Téléphone : </strong><a href="tel:$userPhone">$userPhone</a></p>
                <p><strong>Email : </strong><a href="mailto:$page_author_info->user_email">$page_author_info->user_email</a> </p>
                <p><strong>Campus : </strong>$userLocation</p>
                <p><strong>Tour :</strong>$userTower</p>
                <p><strong>Bureau :</strong>$userOffice</p>
            </div>
        </div>
</div>
HTML;
        return $html;
    } catch (InvalidParameter|MemberNotFound|EntityNotFound $e) {
        return '<div id="message" class="notice notice-error"><p>Une erreur est survenue.</p></div>';
    }

}


/**
 * Shortcode qui affiche l'éditeur de page personnel
 * @return string
 * @throws InvalidParameter
 * @throws MemberNotFound
 */
function edit_personal_page_form():string
{

    //Récupération des données de l'utilisateur
    $current_user_id = wp_get_current_user();
    try {
        $member = Member::findById($current_user_id->ID, "wp");
        //Récupération des champs déjà existant
        $wp_page = $member->get_personal_page_categories();
        wp_enqueue_editor();
        //Création du formulaire
        ob_start();
        ?>
        <form method="POST" action="">

            <div class="role-box">
                <?php foreach ($wp_page as $key => $value) {
                    //Cherche tous les champs de la bd pour les affiché
                    if (strtolower($key) !== "id_page" && strtolower($key) !== "wp_user_id") {
                        $title = ucfirst(strtolower(str_replace('_', ' ', $key)));
                        echo "<h5>$title :</h5>";
                        wp_editor(($value ?? ""), 'personal_page_' . $key . '_editor', array('textarea_name' => 'personal_page_' . $key));
                    }

                } ?>
                <input type="submit" value="Mettre à jour" name="form_submit_personal_page" class="submit-btn">
        </form>
        <?php

        return ob_get_clean();

    } catch (TypeError|InvalidParameter|MemberNotFound $exception) {
        return "<p style='padding: 1%;background-color: orangered'>Vous n'avez pas de page personnel</p>";
    }
}



/**
 * Gère la sauvegarde des données du formulaire de création de page dans la dans la base de donnée.
 * @return void
 */
function handle_personal_page_form(): void
{
    //Gestion des erreurs
    $success_url = home_url("membres-istep/".wp_get_current_user()->user_login."?");
    $error_url = home_url("modifier-votre-page-personnel/?");
    if (isset($_GET['user-create-error'])) {
        $error = sanitize_text_field($_GET['user-create-error']);
        switch ($error) {
            case "1":
                echo "<div class=\"user-update-error\">La localisation entrée n'éxiste pas</div>";
                break;
            case "2":
                echo "<div class=\"user-update-error\">Le numéro de téléphone est incorrecte</div>";
                break;
            case "3":
                echo "<div class=\"user-update-error\">L'extension de l'image n'est pas correcte</div>";
                break;
            case "4":
                echo "<div class=\"user-update-error\">Erreur lors de l'ajout de l'avatar : ".sanitize_text_field($_GET["error-message"])."</div>";
                break;
            case "5":
                echo "<div class=\"user-update-error\">Erreur lors de la mis à jour</div>";
                break;
        }
        if (isset($_GET['user-update-success'])) {
            echo "<div class=\"user-update-success\">L'Mis à jour effectué avec succès</div>";
        }
    }
    //Donnée bibliographique
    if (isset($_POST["form_submit_personal_page"])) {
        $data = array();

        //Récupération des données envoyé via le formulaire
        foreach ($_POST as $post_data_key => $post_data_value) {
            if (str_starts_with($post_data_key, 'personal_page_')) {
                $data[str_replace('personal_page_', '', $post_data_key)] = wp_kses_post($post_data_value);
            }
        }

        //Sauvegarde des données
        try {
            PersonalPage::save($data);
        } catch (UpdateError $e) {
            wp_redirect($error_url."user-update-error=5", 302);
            exit();
        }
        wp_redirect($success_url."user-update-success=0", 302);
    }
}
add_action('wp', 'handle_personal_page_form');



/**
 * Affiche les données enregistré dans l'entité page_personel
 * @return string
 */
function display_section_personal_pages(): string
{
    $current_page_slug = get_post_field('post_name', get_queried_object_id());
    $user = get_user_by('login', $current_page_slug);
    $member = Member::findById($user->ID, "wp");
    $sections = $member->get_personal_page_categories();
    $html = "<div>";
    foreach ($sections as $key => $section) {
        if(strtolower($key) !== "id_page" && strtolower($key) !== "wp_user_id") {
            $title = ucfirst(strtolower(str_replace('_', ' ', $key)));
            if ($section != "" && $section != null) {
                $html .= <<<HTML
                <h5>$title</h5>
                <div>$section</div>
HTML;
            }
        }
    }
    if ($html == "<div>") {
        return "<h4>Aucune donnée disponible</h4>";
    }
    $html.="</div>";
    return $html;
}
add_shortcode('personal_page_display', 'display_section_personal_pages');

/**
 * Shortcode qui vérifie si le bouton d'édition de page doit s'afficher pour l'utilisateur courant
 * Si un "error" c'est que la page "modifier-votre-page-personnel" n'éxiste plus
 * @return string
 */
function display_button_to_edit_personal_pages(): string
{

    $current_page_slug = get_post_field('post_name', get_queried_object_id());
    $user = get_user_by('login', $current_page_slug);
    if (wp_get_current_user() == $user) {
        $page_url = get_permalink(get_page_by_path("modifier-votre-page-personnel"));
        if (!empty($page_url)) {
            return '<a href="' . esc_url($page_url) . '" class="button">Modifier votre page</a>';
        } else {
            return "error";
        }
    }
    return "";
}

add_shortcode('edit_personal_page_btn', 'display_button_to_edit_personal_pages');
add_shortcode('personal_page_form', 'edit_personal_page_form');


/**
 * Ajoute dans la barre administrateur un lien vers leur page personnel
 * @return void
 */
function edit_button_admin_bar(WP_Admin_Bar $admin_bar): void
{
    $current_user = wp_get_current_user();

    if (get_page_by_path("/membres-istep/" . $current_user->user_login) !== null) {
        $args = array(
            'id' => 'personal_page',
            'title' => 'Page perso',
            'href' => '/' . $current_user->user_login . "/",
        );
        $admin_bar->add_menu($args);

        $args = array(
            'id' => 'edit_personal_page',
            'title' => 'Modifier la page perso',
            'href' => '/modifier-votre-page-personnel/',
        );
        $admin_bar->add_menu($args);
    }

}

add_action('admin_bar_menu', 'edit_button_admin_bar', 999);
