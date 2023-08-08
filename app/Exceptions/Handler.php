<?php

namespace App\Exceptions;

use App\Traits\ApiResponser;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class Handler extends ExceptionHandler
{
    use ApiResponser;
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (ValidationException $e, $request) {
            return $this->convertExceptionToResponse($e, $request);
        });

        $this->renderable(function (ModelNotFoundException $e, $request) {
            return $this->errorResponse("No se encontró la id en el modelo especificada", 404);
        });

        $this->renderable(function (AuthenticationException $e, $request) {
            return $this->unauthenticated($request, $e);
        });

        $this->renderable(function (AuthorizationException $e) {
            return $this->errorResponse("No posee permisos para ejecutar esta acción", 403);
        });

        $this->renderable(function (NotFoundHttpException $e, $request) {
            $previous_e = $e->getPrevious();
            if ($previous_e instanceof ModelNotFoundException) {
                return $this->errorResponse("No existe ninguna instancia de {$previous_e->getModel()} con el id especificado", 404);
            }
            return $this->errorResponse("No se encontró la URL especificada", 404);
        });

        $this->renderable(function (MethodNotAllowedHttpException $e, $request) {
            return $this->errorResponse("El método especificado no es válido", 405);
        });

        $this->renderable(function (HttpException $e, $request) {
            return $this->errorResponse($e->getMessage(), $e->getStatusCOde());
        });

        $this->renderable(function (QueryException $e, $request) {
            $codigo = $e->errorInfo[1];

            if ($codigo == 1451) {
                return $this->errorResponse("No se puede eliminar de forma permanente el recurso porque está relacionado con algún otro.", 409);
            }
        });

        $this->renderable(function (Exception $e) {
            return $this->errorResponse("Falla inesperada. Intente luego.${e}", 500);
        });
    }

    protected function unauthenticated($request, AuthenticationException $exception)
    {
        return $this->errorResponse("No autenticado", 401);
    }

    protected function convertValidationExceptionToResponse(ValidationException $e, $request)
    {
        $errors = $e->validator->errors()->getMessages();

        if ($this->isFrontend($request)) {
            return $request->ajax() ? response()->json($errors, 422) : redirect()
                ->back()
                ->withInput($request->input())
                ->withErrors($errors);
        }

        return $this->errorResponse($errors, 422);
    }
}
