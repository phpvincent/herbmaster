<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Dotenv\Exception\ValidationException;
class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * @param Exception $exception
     * @return mixed|void
     * @throws Exception
     */
    public function report(Exception $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $exception
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $exception)
    {
        // 参数验证错误的异常，我们需要返回 400 的 http code 和一句错误信息
        if ($exception instanceof ValidationException) {
            return code_response('10002',array_first(array_collapse($exception->errors())),400);
            return response(['error' => array_first(array_collapse($exception->errors()))], 400);
        }
        // 用户认证的异常，我们需要返回 401 的 http code 和错误信息
        if ($exception instanceof UnauthorizedHttpException) {
            return code_response('10001',$exception->getMessage(),401);
            return response($exception->getMessage(), 401);
        }
        return parent::render($request, $exception);
          if($request->is('api/*')||$request->is('admin/*')){
            $response = [];
            $error = $this->convertExceptionToResponse($exception);
            $response['status'] = $error->getStatusCode();
            $response['msg'] = 'something error';
            if(config('app.debug')) {
                $response['msg'] = empty($exception->getMessage()) ? 'something error' : $exception->getMessage();
                if($error->getStatusCode() >= 500) {
                    if(config('app.debug')) {
                        $response['trace'] = $exception->getTraceAsString();
                        $response['code'] = $exception->getCode();
                    }
                }
            }
            $response['data'] = [];
            return response()->json($response, $error->getStatusCode());
        }
    }
}
