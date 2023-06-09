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
     * @return array<self>
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

    /**
     * Enregistre ou ajoute l'instance dans la base de donnée
     * @return void
     */
    public function save():void;

    /**
     * Supprime l'instance de la base de donnée
     * @return bool Retourne true si la suppression à bien eu lieu, false sinon
     */
    public function delete():bool;
    /**
     * Récupère le dernier id de la table et y ajoute un si aucun id n'est fournis.
     * @param int $id
     * @return int
     */
    function getLastId(int $id):int;
}