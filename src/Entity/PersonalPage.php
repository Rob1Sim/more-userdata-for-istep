<?php

namespace MUDF_ISTEP\Entity;

use MUDF_ISTEP\Exception\UpdateError;
use MUDF_ISTEP\Interface\IWpEntity;
/**
 * Représente l'entité personal_page_ISTeP de la base de données
 */
class PersonalPage
{

    /**
     * @inheritDoc
     */
    static function getTableName(): string
    {
        global $wpdb;
        return $wpdb->prefix . 'personal_page_ISTeP';
    }

    /**
     * @inheritDoc
     */
    public static function save(array $data): void
    {
        global $wpdb;
        $update = $wpdb->update(
            self::getTableName(),
            $data,
            array(
                "wp_user_id"=>get_current_user_id()
            )
        );
        if (gettype($update) =="boolean" && !$update){
            throw new UpdateError("Echec de l'enregistrement de la page personnel");
        }
    }



    /**
     * Récupère toutes les infos présente sur la page de l'utilisateur et les renvoie sous la forme d'un tableau
     * @param int $id l'id de l'utilisateur
     * @return array
     */
    function get_user_personal_pages_categories(int $id):array
    {
        global $wpdb;
        $table = self::getTableName();
        $results = $wpdb->get_results("SELECT * FROM $table where wp_user_id = $id");
        $data = array(); // Tableau pour stocker les résultats
        if (!empty($results)) {
            $data = get_object_vars($results[0]);
        }
        return $data;
    }

    /**
     * Créer une page personnel
     * Si une existe déjà,ce contente de la ette à jour
     * @param string $userDisplayName
     * @param string $login
     * @param int $userId L'id WP de l'utilisateur
     * @return void
     */
    public static function create_personal_page(string $userDisplayName, string $login, int $userId): void
    {

        global $wpdb;
        $table_name = self::getTableName();
        $qr = $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE wp_user_id = $userId");
        if (isset($qr) && intval($qr) == 0){
            $wpdb->insert($table_name, array(
                "wp_user_id"=>$userId,
            ));
        }

        $parent = get_page_by_path('membres-istep');

        $content = "[istep_user_data]
                [personal_page_display]
                [edit_personal_page_btn]";

        $page_data = array(
            'post_title' => $userDisplayName,
            'post_content' => $content,
            'post_status' => 'publish',
            'post_type' => 'page',
            'post_author'=>1,
            'post_name' => $login,
            'post_parent' => $parent->ID,
        );

        // Insère la page dans la base de données de WordPress
        wp_insert_post($page_data);

    }

    /**
     * Créer la page de modification de page personel, elle est unique donc disponible pour tous le monde à la même adresse
     * @return void
     */
    public static function create_modify_personal_page(): void
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
}