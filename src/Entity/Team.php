<?php

namespace MUDF_ISTEP\Entity;

use MUDF_ISTEP\Exception\TeamNotFound;
use MUDF_ISTEP\Interface\IWpEntity;

/**
 * Représente l'entité equipe_ISTeP de la base de données
 */
class Team implements IWpEntity
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
     * @throws TeamNotFound
     */
    public static function findById(int $id): self
    {
        global $wpdb;
        $tableName =self::getTableName();
        $wp_obj = $wpdb->get_results("SELECT * FROM $tableName WHERE id_equipe = $id")[0];
        if (isset($wp_obj)){
            return self::createEntityFromWPDB($wp_obj);
        }
        throw new TeamNotFound("L'id ne correspond à aucune équipe");
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
     * Renvoie le nom de chaque équipe
     * @return array<string>
     */
    public static function getAllNames(): array{
        $teams = self::getAll();
        $teams_names = [];
        foreach ($teams as $team){
            $teams_names[] = $team->getName();
        }
        return $teams_names;
    }

    /**
     * @inheritDoc
     */
    public static function createEntityFromWPDB($entity): self
    {
        return new Team($entity->id_equipe,$entity->nom_equipe);
    }

    /**
     * Vérifie que l'id de l'équipe entrée existe
     * @param int $id
     * @return bool
     */
    public static function isTeamValid(int $id){
        $teams = self::getAll();
        $array_of_id = [];
        foreach ($teams as $team) {
            $array_of_id[] = $team->getId();
        }

        return in_array($id, $array_of_id);
    }
    public function save():void{
        global $wpdb;
        $table_name = self::getTableName();
        $rq = $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE id_equipe = $this->id");
        if (isset($rq) && $rq>0){
            $wpdb->update($table_name, array(
                "nom_equipe"=>$this->name,
            ), array(
                "id_equipe"=>$this->id
            ));
        }else {
            $wpdb->insert($table_name, array(
                "nom_equipe" => $this->name,
            ));
        }
    }
    public function delete():bool{
        global $wpdb;
        $table_name = self::getTableName();
        $rq = $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE id_equipe = $this->id");
        if (isset($rq) && $rq>0){
            $wpdb->delete(
                $table_name,
                array(
                    'id_equipe' => $this->id
                )
            );
            return true;
        }
        return false;
    }
    static function getTableName(): string
    {
        global $wpdb;
        return $wpdb->prefix . 'equipe_ISTeP';
    }
    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }
}