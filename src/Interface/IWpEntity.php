<?php

namespace MUDF_ISTEP\Interface;

use MUDF_ISTEP\Exception\EntityNotFound;

interface IWpEntity
{
    /**
     * Renvoie l'instance contenant cette id, ou une erreur si l'id n'est pas trouvé
     * @param int $id
     * @return self
     * @throws EntityNotFound
     */
    public static function findById(int $id):self;

    /**
     * Récupère toutes les entrée de l'entité dans la base de donnée
     * @return array
     */
    public static function getAll():array;

    /**
     * Créer une entité à partir des objets de la base de données WP
     * @param $entity
     * @return self
     */
    public static function createEntityFromWPDB($entity):self;

    /**
     * Retourne le nom de la table de la classe dans la base de donnée
     * @return string
     */
    static function getTableName():string;
}