<?php

namespace App\Services;

use App\Entity\TodoListMatchDeveloper;
use App\Repository\TodoListMatchDeveloperRepository;
use App\Repository\ToDoListRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Hungarian\Hungarian;


class ToDoListMatchDeveloperService implements ToDoListMatchDeveloperServiceInterface
{

    /**
     * @var array
     */
    private $developerGroup = [];
    /**
     * @var array
     */
    private $jobsGroup = [];
    /**
     * @var
     */
    private $developers;
    /**
     * @var
     */
    private $jobs;

    /**
     * @var array
     */
    private $toDoList;
    /**
     * @var ToDoListRepository
     */
    private $toDoListRepository;
    /**
     * @var UserRepository
     */
    private $userRepository;
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;
    /**
     * @var TodoListMatchDeveloperRepository
     */
    private $listMatchDeveloperRepository;


    /**
     * @param ToDoListRepository $toDoListRepository
     * @param UserRepository $userRepository
     * @param EntityManagerInterface $entityManager
     * @param TodoListMatchDeveloperRepository $listMatchDeveloperRepository
     */
    public function __construct(
        ToDoListRepository               $toDoListRepository,
        UserRepository                   $userRepository,
        EntityManagerInterface           $entityManager,
        TodoListMatchDeveloperRepository $listMatchDeveloperRepository
    )
    {
        $this->toDoListRepository = $toDoListRepository;
        $this->userRepository = $userRepository;
        $this->entityManager = $entityManager;
        $this->listMatchDeveloperRepository = $listMatchDeveloperRepository;
    }

    /**
     * @return bool
     * @throws \Exception
     */
    public function matchJobAndDevelopers()
    {
        $this->setJobs($this->toDoListRepository->findAll());
        $this->setDevelopers($this->userRepository->findAll());
        $this->toDoList = array_chunk($this->getJobs(), count((array)$this->getDevelopers()));
        $this->listMatchDeveloperRepository->removeAllTodo();

        foreach ($this->jobsToDeveloper() as $item) {
            $jobs = new TodoListMatchDeveloper();
            $jobs->setUserId($item['developerId'])
                ->setTodoId($item['jobId'])
                ->setRank($item['rank'])
                ->setEffort($item['effort']);
            $this->insertMatchTodoList($jobs);
        }
        return true;
    }

    /**
     * @param TodoListMatchDeveloper $developer
     * @throws \Doctrine\DBAL\ConnectionException
     */
    private function insertMatchTodoList(TodoListMatchDeveloper $developer)
    {
        $autoCommitFlagBackup = $this->entityManager->getConnection()->isAutoCommit();
        $this->entityManager->getConnection()->beginTransaction();
        $this->entityManager->getConnection()->setAutoCommit(false);
        try {
            $this->entityManager->persist($developer);
            $this->entityManager->flush();
            $this->entityManager->getConnection()->commit();
            $this->entityManager->getConnection()->setAutoCommit($autoCommitFlagBackup);
        } catch (\Exception $e) {
            $this->entityManager->getConnection()->rollBack();
            $this->entityManager->getConnection()->setAutoCommit($autoCommitFlagBackup);
            throw $e;
        }
    }

    /**
     * @return array
     */
    public function createMatrix(): array
    {
        $allMatrix = [];
        $matrixDesc = 0;
        foreach ($this->toDoList as $toDoList) {
            $addMatrix = $this->oneMatrix($matrixDesc, $toDoList);
            $allMatrix[] = $addMatrix;
            $matrixDesc++;
        }

        return $allMatrix;
    }

    /**
     * @param $matrixDesc
     * @param $toDoList
     * @return array
     */
    private function oneMatrix($matrixDesc, $toDoList): array
    {
        $rank = 0;
        $matrix = [];
        foreach ($this->developers as $developer) {
            $this->developerGroup[$matrixDesc][$rank] = $developer;
            if (count($this->developers) > count($toDoList)) {
                for ($i = 1; $i <= count($this->developers) - count($toDoList); $i++) {
                    $toDoList[] = range(999, 999 + count($this->developers));
                }
            }
            $rank2 = 0;
            $rowData = [];
            foreach ($toDoList as $todo) {
                if (is_object($todo)) {
                    $this->jobsGroup[$matrixDesc][$rank][$rank2] = $todo;
                    $rowData[] = $this->developerYield($developer, $todo);
                } else {
                    $rowData[] = 99999;
                }
                $rank2++;
            }

            $rank++;
            $matrix[] = $rowData;
        }

        return $matrix;
    }

    /**
     * @param $developer
     * @param $todo
     * @return int
     */
    private function developerYield($developer, $todo): int
    {
        // yazilimcinin is icin hesaplanan verimi
        $yield = ($todo->getEstimatedDuration() * $todo->getLevel()) / $developer->getEstimatedDuration();
        return (integer)round($yield);
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function jobsToDeveloper(): array
    {
        $_return = [];
        $e = $this->createMatrix();
        foreach ($e as $k => $createMatris) {
            $hungarian = new Hungarian($createMatris);
            $allocation = $hungarian->solveMin();

            foreach ($allocation as $dev => $job) {
                $developer = $this->developerGroup[$k][$dev];
                $jobs = !empty($this->jobsGroup[$k][$dev][$job]) ? $this->jobsGroup[$k][$dev][$job] : null;
                if ($developer && $jobs) {
                    $_return[] = [
                        'rank' => $k,
                        'developerId' => $developer->getId(),
                        'jobId' => $jobs->getId(),
                        'effort' => $this->developerYield($developer, $jobs),
                    ];
                }
            }
        }

        return $_return;
    }

    /**
     * @return mixed
     */
    public function getDevelopers()
    {
        return $this->developers;
    }

    /**
     * @param mixed $developers
     */
    public function setDevelopers($developers): void
    {
        $this->developers = $developers;
    }

    /**
     * @return mixed
     */
    public function getJobs()
    {
        return $this->jobs;
    }

    /**
     * @param mixed $jobs
     */
    public function setJobs($jobs): void
    {
        $this->jobs = $jobs;
    }

    /**
     * @return array
     */
    public function getTreeJobs(): array
    {
        return $this->toDoList;
    }

    /**
     * @param array $toDoList
     */
    public function setTreeJobs(array $toDoList): void
    {
        $this->toDoList = $toDoList;
    }

}