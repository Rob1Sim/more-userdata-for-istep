<?php

namespace MUDF_ISTEP\Entity;

use MUDF_ISTEP\Exception\LocationNotFound;
use MUDF_ISTEP\Interface\IWpEntity;

class Location implements \MUDF_ISTEP\Interface\IWpEntity
{
    private int $id;
    private string $name;

    /**
     * @param int $id
     * @param string $name
     */
    public function __construct(int $id, string $name)
    {
        $this->id = $id;
        $this->name = $name;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @inheritDoc
     */
    public static function findById(int $id): self
    {
        global $wpdb;
        $tableName = self::getTableName();
        $wp_obj = $wpdb->get_results("SELECT * FROM $tableName WHERE id_localisation = $id")[0];
        if (isset($wp_obj)){
            return self::createEntityFromWPDB($wp_obj);
        }
        throw new LocationNotFound("L'id ne correspond à aucun campus");
    }

    /**
     * @inheritDoc
     */
    public static function getAll(): array
    {
        $instance_list = [];
        foreach (get_list_of_table(self::getTableName()) as $wp_objet){
            $instance_list[] = self::createEntityFromWPDB($wp_objet);
        }
        return $instance_list;
    }

    /**
     * @inheritDoc
     */
    public static function createEntityFromWPDB($entity): self
    {
        return new Location($entity->id_localisation,$entity->nom_localisation);
    }

    /**
     * Vérifie si un campus existe
     * @param int $id
     * @return bool
     */
    public static function is_location(int $id):bool{
        global $wpdb;
        $table_name = self::getTableName();
        $wp_obj = $wpdb->get_results("SELECT * FROM $table_name WHERE id_localisation = $id")[0];
        return !empty($wpdb->get_results($wp_obj));
    }
    /**
     * @inheritDoc
     */
    static function getTableName(): string
    {
        global $wpdb;
        return $wpdb->prefix . 'localisation_ISTeP';
    }
}