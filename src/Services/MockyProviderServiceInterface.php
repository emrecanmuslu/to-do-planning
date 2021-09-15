<?php

namespace App\Services;

use App\Repository\UserRepository;

interface MockyProviderServiceInterface
{
    public function saveProviderITTodoList($todos);

    public function saveProviderBusinessTodoList($todos);

    public function getPlanningTodoList();
}