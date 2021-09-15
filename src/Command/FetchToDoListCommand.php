<?php

namespace App\Command;

use App\Services\ApiResponseServiceInterface;
use App\Services\MockyProviderServiceInterface;
use App\Services\ToDoListMatchDeveloperServiceInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class FetchToDoListCommand extends Command
{
    protected static $defaultName = 'mocky:getTodoList';
    protected static $defaultDescription = 'fetch mocky provider to-do list';

    /**
     * @var ApiResponseServiceInterface
     */
    private $apiResponse;

    /**
     * @var MockyProviderServiceInterface
     */
    private $mockyProviderService;
    /**
     * @var ToDoListMatchDeveloperServiceInterface
     */
    private $matchDeveloperService;

    /**
     * @param ApiResponseServiceInterface $apiResponse
     * @param MockyProviderServiceInterface $mockyProviderService
     * @param ToDoListMatchDeveloperServiceInterface $matchDeveloperService
     * @param string|null $name
     */
    public function __construct(
        ApiResponseServiceInterface            $apiResponse,
        MockyProviderServiceInterface          $mockyProviderService,
        ToDoListMatchDeveloperServiceInterface $matchDeveloperService,
        string                                 $name = null
    )
    {
        parent::__construct($name);
        $this->apiResponse = $apiResponse;
        $this->mockyProviderService = $mockyProviderService;
        $this->matchDeveloperService = $matchDeveloperService;
    }

    protected function configure(): void
    {
        $this
            ->setDescription(self::$defaultDescription);
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|string
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        try {
            /*PROVIDER 1*/
            $mockyProviderBusinessTodoList = $this->apiResponse->apiGet($_ENV['APP_PROVIDER_BUSINESS']);
            if (count($mockyProviderBusinessTodoList) > 0)
                $this->mockyProviderService->saveProviderBusinessTodoList($mockyProviderBusinessTodoList);

            /*PROVIDER 2*/
            $mockyProviderITTodoList = $this->apiResponse->apiGet($_ENV['APP_PROVIDER_IT']);
            if (count($mockyProviderITTodoList) > 0)
                $this->mockyProviderService->saveProviderITTodoList($mockyProviderITTodoList);

            $this->matchDeveloperService->matchJobAndDevelopers();
        } catch (\Exception $e) {
            return $e->getMessage();
        }


        $io->success('The list has been successfully retrieved.');
        return 0;
    }
}
