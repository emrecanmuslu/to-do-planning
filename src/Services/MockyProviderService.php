<?php

namespace App\Services;

use App\Entity\ToDoList;
use App\Repository\TodoListMatchDeveloperRepository;
use App\Repository\ToDoListRepository;
use App\Repository\UserRepository;
use Doctrine\DBAL\ConnectionException;
use Doctrine\ORM\EntityManagerInterface;

class MockyProviderService implements MockyProviderServiceInterface
{

    CONST WEEKLY_WORKING_HOURS = 45;

    protected $toDoListRepository;
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;
    /**
     * @var UserRepository
     */
    private $userRepository;
    /**
     * @var TodoListMatchDeveloperRepository
     */
    private $listMatchDeveloperRepository;


    /**
     * @param ToDoListRepository $toDoListRepository
     * @param UserRepository $userRepository
     * @param TodoListMatchDeveloperRepository $listMatchDeveloperRepository
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(
        ToDoListRepository $toDoListRepository,
        UserRepository $userRepository,
        TodoListMatchDeveloperRepository $listMatchDeveloperRepository,
        EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->userRepository = $userRepository;
        $this->toDoListRepository = $toDoListRepository;
        $this->listMatchDeveloperRepository = $listMatchDeveloperRepository;
    }


    /**
     * @param $todos
     * @throws \Exception
     */
    public function saveProviderITTodoList($todos)
    {
        foreach ($todos as $todo) {
            $task = new ToDoList();
            $task->setTitle($todo['id']);
            $task->setLevel($todo['zorluk']);
            $task->setEstimatedDuration($todo['sure']);

            $existingOne = $this->toDoListRepository->findOneBy([
                'title' => $task->getTitle()
            ]);

            if (null !== $existingOne) {
                continue;
            }

            $this->insertTodoList($task);
        }
    }


    /**
     * @param $todos
     * @throws \Exception
     */
    public function saveProviderBusinessTodoList($todos)
    {
        foreach ($todos as $todo) {
            $keyName = array_key_first($todo);
            $task = new ToDoList();
            $task->setTitle($keyName);
            $task->setLevel($todo[$keyName]['level']);
            $task->setEstimatedDuration($todo[$keyName]['estimated_duration']);

            $existingOne = $this->toDoListRepository->findOneBy([
                'title' => $task->getTitle()
            ]);

            if (null !== $existingOne) {
                continue;
            }

           $this->insertTodoList($task);
        }
    }

    /**
     * @param $task
     * @throws ConnectionException
     */
    private function insertTodoList($task)
    {
        $autoCommitFlagBackup = $this->entityManager->getConnection()->isAutoCommit();
            $this->entityManager->getConnection()->beginTransaction();
            $this->entityManager->getConnection()->setAutoCommit(false);
            try {
                $this->entityManager->persist($task);
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
    public function getPlanningTodoList(): array
    {
        $developers = $this->userRepository->findAll();
        $matchedDevelopers = $this->listMatchDeveloperRepository->findBy([], ['rank' => 'asc']);
        $jobs = $this->toDoListRepository->findAll();

        $developerSheme = $this->createDeveloperSchema($developers);
        $jobSheme = $this->createJobSchema($jobs);
        $todoList = $this->matchedJobAndDeveloper($matchedDevelopers, $jobSheme, $developerSheme);
        $weeklyPlan = [];
        $totalWeek = 0;
        foreach ($todoList as $todoMap) {
            $plan = [];
            $totalEffort = 0;
            $week = 1;
            $key = $todoMap['developer']->getId();

            foreach ($todoList[$key]['jobs'] as $job) {
                $effort = $job['effort'];
                $job['totalEffort'] = $effort;

                if ($totalEffort >= 45) {
                    $week += 1;
                    $totalEffort = 0;
                }

                $endWeeklyEffort = $totalEffort;
                $totalEffort += $effort;

                if ($totalEffort > self::WEEKLY_WORKING_HOURS) {
                    $job['runTime'] = self::WEEKLY_WORKING_HOURS - $endWeeklyEffort;
                    $plan[$week][] = $job;
                    $totalEffort = $effort - (self::WEEKLY_WORKING_HOURS - $endWeeklyEffort);
                    $job['runTime'] = $totalEffort;
                    $week += 1;
                }

                $plan[$week][] = $job;
            }


            $weeklyPlan[$key] = [
                'developer' => [
                    'username' => $todoMap['developer']->getUsername(),
                    'estimated_duration' => $todoMap['developer']->getEstimatedDuration()
                ],
                'plan' => $plan,
                'totalWeek' => count($plan)
            ];
            if($totalWeek < count($plan)){
                $totalWeek = count($plan);
            }
        }
        $mergePlan['totalWeek'] = $totalWeek;
        $mergePlan['list'] = $weeklyPlan;

        return $mergePlan;
    }

    /**
     * @param $matchedDevelopers
     * @param $jobSheme
     * @param $developerSheme
     * @return array
     */
    private function matchedJobAndDeveloper($matchedDevelopers, $jobSheme, $developerSheme): array
    {
        foreach ($matchedDevelopers as $developer) {
            $getJob = $jobSheme[$developer->getTodoId()];

            $developerSheme[$developer->getUserId()]['jobs'][$developer->getRank()] = [
                'title' => $getJob->getTitle(),
                'estimated_duration' => $getJob->getEstimatedDuration(),
                'level' => $getJob->getLevel(),
                'effort' => $developer->getEffort()
            ];
        }

        return $developerSheme;
    }

    /**
     * @param $developers
     * @return array
     */
    private function createDeveloperSchema($developers): array
    {
        $developerMaps = [];
        foreach ($developers as $developer) {
            $key = $developer->getId();
            $add = [
                'developer' => $developer,
                'jobs' => [],
            ];
            $developerMaps[$key] = $add;
        }

        return $developerMaps;
    }

    /**
     * @param $jobs
     * @return array
     */
    private function createJobSchema($jobs): array
    {
        $jobMaps = [];
        foreach ($jobs as $job) {
            $key = $job->getId();
            $jobMaps[$key] = $job;
        }

        return $jobMaps;
    }
}