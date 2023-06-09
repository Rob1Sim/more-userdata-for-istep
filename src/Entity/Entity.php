<?php

namespace MUDF_ISTEP\Entity;

use MUDF_ISTEP\Interface\IWpEntity;

abstract class Entity implements IWpEntity
{
    /**
     * @return int
     */
    public abstract function getId(): int;


    /**
     * Récupère le dernier id de la table et y ajoute un si aucun id n'est fournis.
     * @param int $id
     * @param string $id_name le nom du champs dans la bd
     * @return int
     */
    protected function getLastId(int $id, string $id_name):int{
        if ($id == -1){
            global $wpdb;
            $table_name = self::getTableName();
            $id = $wpdb->get_var("SELECT MAX($id_name) FROM $table_name");
            $id = intval($id) + 1?? 0;
        }
        return $id;
    }

    /**
     * @inheritDoc
     */
    public abstract static function findById(int $id): self;


    /**
     * @inheritDoc
     */
    public abstract static function getAll(): array;


    /**
     * @inheritDoc
     */
    public abstract static function createEntityFromWPDB($entity): self;


    /**
     * @inheritDoc
     */
    static abstract function getTableName(): string;


    /**
     * @inheritDoc
     */
    public abstract function save(): void;


    /**
     * @inheritDoc
     */
    public abstract function delete(): bool;
}