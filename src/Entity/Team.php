<?php

namespace MUDF_ISTEP\Entity;

use MUDF_ISTEP\Interface\IWpEntity;

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
     */
    public static function findById(int $id): \MUDF_ISTEP\Interface\IWpEntity
    {
        // TODO: Implement findById() method.
    }

    /**
     * @inheritDoc
     */
    public static function getAll(): array
    {
        // TODO: Implement getAll() method.
    }

    /**
     * @inheritDoc
     */
    public static function createEntityFromWPDB($entity): \MUDF_ISTEP\Interface\IWpEntity
    {
        // TODO: Implement createEntityFromWPDB() method.
    }
}