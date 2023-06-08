<?php
namespace MUDF_ISTEP\Entity;
use MUDF_ISTEP\Exception\EntityNotFound;
use MUDF_ISTEP\Exception\InvalidParameter;
use MUDF_ISTEP\Exception\MemberNotFound;
use MUDF_ISTEP\Exception\TeamNotFound;
use MUDF_ISTEP\Interface\IWpEntity;

/**
 * Représente l'entité membre_ISTeP de la base de données
 */
class Member implements IWpEntity
{
    private int $id;
    private int $wp_id;
    private string $function;
    private string $phone;
    private string $office;
    private string $officeTower;
    private int $location;
    private string $employer;
    private string $mailCase;
    private string $teamRank;

    /**
     * @param int $id
     * @param int $wp_id
     * @param string $function
     * @param string $phone
     * @param string $office
     * @param string $officeTower
     * @param int $location
     * @param string $employer
     * @param string $mailCase
     */
    public function __construct(int    $id, int $wp_id, int $location,
                                string $function = "", string $phone ="",
                                string $office = "", string $officeTower ="",
                                string $employer ="", string $mailCase ="", string $teamRank = "")
    {
        $this->id = $id;
        $this->wp_id = $wp_id;
        $this->function = $function;
        $this->phone = $phone;
        $this->office = $office;
        $this->officeTower = $officeTower;
        $this->location = $location;
        $this->employer = $employer;
        $this->mailCase = $mailCase;
        $this->teamRank = $teamRank;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getWpId(): int
    {
        return $this->wp_id;
    }

    /**
     * @return string
     */
    public function getFunction(): string
    {
        return $this->function;
    }

    /**
     * @return string
     */
    public function getPhone(): string
    {
        return $this->phone;
    }

    /**
     * @return string
     */
    public function getOffice(): string
    {
        return $this->office;
    }

    /**
     * @return string
     */
    public function getOfficeTower(): string
    {
        return $this->officeTower;
    }

    /**
     * @return IWpEntity|Location
     * @throws EntityNotFound
     */
    public function getLocation(): Location|IWpEntity
    {
        return Location::findById($this->location);
    }

    /**
     * @return string
     */
    public function getEmployer(): string
    {
        return $this->employer;
    }

    /**
     * @return string
     */
    public function getMailCase(): string
    {
        return $this->mailCase;
    }
    /**
     * @return string
     */
    public function getTeamRank(): string
    {
        return $this->teamRank;
    }


    /**
     *  Renvoie l'instance de l'utilisateur qui possède l'id passé en paramètre
     * @param string $type n'accepte que "istep" ou "wp", choisi par quel id l'utilisateur doit être chercher
     * @throws InvalidParameter
     * @throws MemberNotFound
     */
    public static function findById(int $id, string $type="istep"): Member
    {
        global $wpdb;
        $tableName = self::getTableName();
        if ($type == "wp") {
            $wp_objet = $wpdb->get_results("SELECT * FROM $tableName WHERE wp_user_id = $id")[0];
            if (isset($wp_objet)){
                return Member::createEntityFromWPDB($wp_objet);
            }
            throw new MemberNotFound("L'id entrée est incorrecte");
        }
        if ($type == "istep") {
            $wp_objet = $wpdb->get_results("SELECT * FROM $tableName WHERE id_membre = $id")[0];
            if (isset($wp_objet)){
                return self::createEntityFromWPDB($wp_objet);
            }
            throw new MemberNotFound("L'id entrée est incorrecte");
        }
        throw new InvalidParameter("Type incorrecte : le paramètre type ne prend que la valeur wp ou istep");
    }

    public static function getAll(): array
    {
        $instance_list = [];
        foreach (get_list_of_table(self::getTableName()) as $wp_objet){
            $instance_list[] = self::createEntityFromWPDB($wp_objet);
        }
        return $instance_list;
    }

    public static function createEntityFromWPDB($entity): Member
    {
        return new Member($entity->id_membre,$entity->wp_user_id,
            $entity->campus_location,($entity->fonction ?? ""),($entity->nTelephone ?? ""),
            ($entity->bureau ?? ""),($entity->rangEquipe ?? ""),($entity->tourDuBureau ?? ""),
            ($entity->employeur ?? ""),($entity->rangEquipe ?? ""));
    }

    /**
     * Renvoie une liste contenant les instances des éuqipes de l'utilisateur
     * @return array<Team>
     */
    public function getTeams():array{
        global $wpdb;
        $table_name = self::getTeamMemberRelationTableName();

        $teams = $wpdb->get_results("SELECT id_equipe FROM $table_name WHERE id_membre = $this->id");
        $teams_object = [];
        foreach ($teams as $team){
            try {
                $teams_object[] = Team::findById($team->id_equipe);
            } catch (TeamNotFound|EntityNotFound $e) {
                $teams_object[] = new Team(0,"Pas d'équipe");
            }
        }
        return $teams_object;
    }

    /**
     * Renvoie la listes des nom des équipes des utilisateurs
     * @return array<string>
     */
    public function getTeamsNames():array{
        $teams = $this->getTeams();
        $teams_names = [];
        foreach ($teams as $team){
            $teams_names[] = $team->getName();
        }
        return $teams_names;
    }

    /**
     * Renvoie la listes des id des équipes des utilisateurs
     * @return array<int>
     */
    public function getTeamsId():array{
        $teams = $this->getTeams();
        $teams_id = [];
        foreach ($teams as $team){
            $teams_id[] = $team->getId();
        }
        return $teams_id;
    }

    /**
     * Renvoie le nom de la tour de façon lisible
     * @return string
     */
    public function getReadableOfficeTower():string{
        $parts = explode('-', $this->officeTower);

        $tour = ucfirst($parts[0]);
        $floor = str_replace('-', ' ', $parts[1]);
        $level = $parts[2];

        return "$tour $floor"."-"." $level"."ème étage";
    }

    /**
     * Ajoute le membre aux équipes
     * @param array $teams_id_list
     * @return void
     */
    public function addTeam(array $teams_id_list):void{
        //Si pour une raison quelconque il n'y a pas d'équipe alors on l'attribut à l'équipe "Pas d'équipe"
        global $wpdb;
        if (count($this->getTeams()) == 0) {
            $teams_id_list[] = 1;
        }
        //Création d'entités entre les équipes et l'utilisateur
        foreach ($teams_id_list as $team) {
            $wpdb->insert(
                self::getTeamMemberRelationTableName(),
                array(
                    'id_equipe' => intval($team),
                    'id_membre' => $this->id
                )
            );
        }
    }

    /**
     * Supprime l'équipe avec l'id passé en paramètre
     * @param int $id
     * @return void
     */
    public function deleteTeam(int $id):void{
        global $wpdb;

        $wpdb->delete(
            TABLE_MEMBERS_TEAM_NAME,
            array(
                "id_equipe" => $this->id,
                "id_membre" => $id
            )
        );
    }

    /**
     * Retourne l'utilisateur WP associé à ce membre
     * @return false|\WP_User
     */
    public function getWPUser():false|\WP_User{
        return get_user_by('id', $this->getWpId());
    }
    static function getTeamMemberRelationTableName():string{
        global $wpdb;
        return $wpdb->prefix . 'membre_equipe_ISTeP';
    }

    static function getTableName(): string
    {
        global $wpdb;
        return $wpdb->prefix . 'equipe_ISTeP';
    }
}