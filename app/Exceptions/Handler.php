<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

class Handler extends ExceptionHandler
{
    protected $dontReport = [
        //
    ];

    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    public function report(Throwable $exception){
        parent::report($exception);
    }
    public function shouldReport(Throwable $exception){
        parent::shouldReport($exception);
    }
    public function render($request, Throwable $exception){
         return parent::render($request, $exception);
    }
    public function renderForConsole($output, Throwable $exception){
        return parent::renderForConsole($output, $exception);
    }
}
