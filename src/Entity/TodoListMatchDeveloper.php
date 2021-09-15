<?php

namespace App\Entity;

use App\Repository\TodoListMatchDeveloperRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=TodoListMatchDeveloperRepository::class)
 */
class TodoListMatchDeveloper
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer")
     */
    private $todo_id;

    /**
     * @ORM\Column(type="integer")
     */
    private $user_id;

    /**
     * @ORM\Column(type="integer")
     */
    private $rank;

    /**
     * @ORM\Column(type="integer")
     */
    private $effort;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTodoId(): ?int
    {
        return $this->todo_id;
    }

    public function setTodoId(int $todo_id): self
    {
        $this->todo_id = $todo_id;

        return $this;
    }

    public function getUserId(): ?int
    {
        return $this->user_id;
    }

    public function setUserId(int $user_id): self
    {
        $this->user_id = $user_id;

        return $this;
    }


    public function getRank()
    {
        return $this->rank;
    }

    public function setRank(int $rank): self
    {
        $this->rank = $rank;

        return $this;
    }


    public function getEffort()
    {
        return $this->effort;
    }

    public function setEffort(int $effort): self
    {
        $this->effort = $effort;

        return $this;
    }



}
