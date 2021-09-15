<?php

namespace App\Http\Exceptions;

use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

class ResourceAlreadyExistsHttpException extends ConflictHttpException
{

}