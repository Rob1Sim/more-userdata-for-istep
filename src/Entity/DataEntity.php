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
     * @return array<object>
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
    abstract public static function findById(int $id): self;


    /**
     * @inheritDoc
     */
    abstract public static function getAll(): array;


    /**
     * @inheritDoc
     */
    abstract public static function createEntityFromWPDB($entity): self;


    /**
     * @inheritDoc
     */
    abstract public static function getTableName(): string;


    /**
     * @inheritDoc
     */
    abstract public function save(): void;


    /**
     * @inheritDoc
     */
    abstract public function delete(): bool;


    /**
     * @inheritDoc
     */
    abstract public function getLastId(int $id): int;

}
