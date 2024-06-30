<?php
// src/Exception/ProjectNotFoundException.php

namespace App\Exception;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ProjectNotFoundException extends NotFoundHttpException
{
    public function __construct(string $message = 'Project not found', \Throwable $previous = null, int $code = 0)
    {
        parent::__construct($message, $previous, $code);
    }
}
