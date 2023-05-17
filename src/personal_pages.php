<?php
/**
 * Gestion des pages personnels
 */
add_shortcode('istep_user_data','display_users_data');
/**
 * Affiche diverses informations de l'utilisateur sur la page de base
 * @return string
 */
function display_users_data(): string
{
    $page_id = get_queried_object_id();
    $page_author_id = get_post_field( 'post_author', $page_id );
    $page_author_info = get_userdata( $page_author_id ); // Récupère les informations de l'utilisateur

    $userData = get_istep_user_by_id($page_author_id);
    $userAvatar = get_user_avatar($page_author_id);
    $userTower = convert_tower_into_readable($userData->tourDuBureau);
    $userTeams = get_user_teams_names_by_user_id($userData->id_membre);





    $html = <<<HTML
    <div class="user-info-container">
        <div>
            $userAvatar
        </div>
        <div class="user-info-text-container">
            <div>
                <h5>Fonction</h5>
                <p>$userData->fonction</p>
                
                <h5>Equipes</h5>
HTML;
    foreach ($userTeams as $userTeam) {
        $html.="<p>$userTeam</p>";
    }
    $html.= <<<HTML
            </div>
            <div>
                <h5>Coordonées :</h5>
                <p><strong>Téléphone : </strong><a href="tel:$userData->nTelephone">$userData->nTelephone</a></p>
                <p><strong>Email : </strong><a href="mailto:$page_author_info->user_email">$page_author_info->user_email</a> </p>
                <p><strong>Campus : </strong>$userData->campus</p>
                <p><strong>Tour :</strong>$userTower</p>
                <p><strong>Bureau :</strong>$userData->bureau</p>
            </div>
        </div>
</div>
HTML;
    return $html;
}

/**
 * Créer une page personnel lors de l'ajout d'un utilisateur via le formulaire
 * @param int $userId
 * @param string $userDisplayName
 * @param string $userNiceName
 * @return void
 */
function create_personal_page(string $userDisplayName,string $userNiceName): void
{
    $parent = get_page_by_path('membres-istep');

    $page_data = array(
        'post_title' => $userDisplayName,
        'post_content' => '[istep_user_data]',
        'post_status' => 'publish',
        'post_type' => 'page',
        'post_name' => $userNiceName,
        'post_parent' => $parent->ID,
    );

// Insère la page dans la base de données de WordPress
    wp_insert_post($page_data);
}


/**
 * Shortcode qui affiche l'éditeur de page personnel
 * @return string
 */
function edit_personal_page_form():string{

    //Récupération des données de l'utilisateur
    $current_user_id = get_current_user_id();
    //Récupération des champs déjà existant
    $wp_page = get_user_personal_pages_categories($current_user_id);
    //Création du formulaire
    //Cherche tous les champs de la bd pour les affiché
    ob_start();
    ?>
    <form method="POST" action="">
        <?php foreach ($wp_page as $key => $value){
            if(strtolower($key) !== "id_page" && strtolower($key) !== "wp_user_id"){
                $title = ucfirst(strtolower(str_replace('_',' ',$key)));
                echo "<h5>$title :</h5>";
                wp_editor(($value ?? ""), 'personal_page_'.$key.'_editor', array('textarea_name' => 'personal_page_'.$key));
            }

        }?>
        <input type="submit" value="Mettre à jour" name="form_submit_personal_page" class="submit-btn">
    </form>
    <?php

    return ob_get_clean();
}

add_shortcode('personal_page_form', 'edit_personal_page_form');

/**
 * Gère la sauvegarde des données du formulaire de création de page dans la dans la base de donnée.
 * @return void
 */
function handle_personal_page_form(): void
{
    if (isset($_POST["form_submit_personal_page"])){
        $data = array();

        //Récupération des données envoyé via le formulaire
        foreach ($_POST as $post_data_key => $post_data_value){
            if (str_starts_with($post_data_key,'personal_page_')){
                $data[str_replace('personal_page_', '', $post_data_key)] = wp_kses_post($post_data_value);
            }
        }

        //Sauvegarde des données
        global $wpdb;
        if (!$wpdb->update(TABLE_PERSONAL_PAGE_NAME,
            $data,
            array(
                "wp_user_id"=>get_current_user_id()
            )))
        {
            echo '<div id="message" class="updated notice"><p>Erreur lors de la mis à jour.</p></div>';
        }else{
            echo '<div id="message" class="updated notice"><p>Mis à jour réussie</p></div>';;
        }
    }
}
add_action('wp','handle_personal_page_form');

/**
 * Créer la page de modification de page personel, elle est unique donc disponible pour tous le monde à la même adresse
 * @return void
 */
function create_modify_personal_page(): void
{

    $page_data = array(
        'post_title' => "Modifier votre page personnel",
        'post_content' => '[personal_page_form]',
        'post_status' => 'publish',
        'post_type' => 'page',
        'post_name' => "modifier-votre-page-personnel",
    );

    wp_insert_post($page_data);
}
