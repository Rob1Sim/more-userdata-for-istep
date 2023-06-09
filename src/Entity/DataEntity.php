<?php

namespace MUDF_ISTEP\Entity;

use MUDF_ISTEP\Interface\IWpEntity;
/**
 * Class mère des class Location, Member et Team
 */
abstract class DataEntity implements IWpEntity
{
    /**
     * Récupère tous les élément d'une table donnée
     * @param string $table
     * @return array
     */
    protected static function get_list_of_table(string $table):array
    {
        global $wpdb;
        $tableName = $table;
        return $wpdb->get_results("SELECT * FROM $tableName");
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


    /**
     * @inheritDoc
     */
    public abstract function getLastId(int $id): int;

}