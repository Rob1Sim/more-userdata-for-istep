<?php
namespace MUDF_ISTEP\Entity;
class Membre
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
                                string $employer ="", string $mailCase ="")
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
}