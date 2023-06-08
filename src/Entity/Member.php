<?php
namespace MUDF_ISTEP\Entity;
use MUDF_ISTEP\Exception\InvalidParameter;
use MUDF_ISTEP\Exception\MemberNotFound;
use MUDF_ISTEP\Interface\IWpEntity;

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
     * @return int
     */
    public function getLocation(): int
    {
        return $this->location;
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
        $tableName = TABLE_MEMBERS_NAME;
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
        foreach (get_list_of_table(TABLE_MEMBERS_NAME) as $wp_objet){
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
}