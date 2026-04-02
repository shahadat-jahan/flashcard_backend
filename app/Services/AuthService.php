<?php

namespace App\Services;

use App\Enums\ServiceResultType as ResultType;
use App\Enums\TokenAbility;
use App\Enums\UserStatus;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\NewPasswordRequest;
use App\Repositories\UserRepository;
use Carbon\Carbon;
use hisorange\BrowserDetect\Facade as Browser;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Mockery\Exception;
use Symfony\Component\HttpFoundation\Response;

class AuthService extends Service
{
    public function __construct(private readonly UserRepository $repository)
    {
        parent::__construct();
    }

    /**
     * User Registration
     */
    public function registerUser(array $data): ServiceResult
    {
        DB::beginTransaction();
        try {
            $user = $this->repository->findUserByEmailWithTrashed($data['email']);
            if (! empty($user->id)) {
                $user = $this->repository->restoreDeletedUser($user, $data);
            } else {
                $user = $this->repository->createUser($data);
            }

            DB::commit();

            event(new Registered($user));
            $this->result->setData(['message' => 'Registration successful.'], ResultType::META);
        } catch (Exception $exception) {
            DB::rollBack();

            $message = 'Registration failed.';
            $this->keepLog('error', $message, $exception->getMessage());
            $this->result->setError($message, Response::HTTP_BAD_REQUEST);
        }

        return $this->result;
    }

    /**
     * User login
     *
     *
     * @throws ValidationException
     */
    public function userLogin(LoginRequest $request): ServiceResult
    {
        try {
            $request->authenticate();

            $user = $request->user();
            $tokens = $this->generateTokens($user);
            $result = $this->prepareLoginResponse($user, $tokens);

            $this->result->setData($result, ResultType::JSON);
        } catch (Exception $exception) {
            $message = 'User login failed.';

            $this->keepLog('error', $message, $exception->getMessage());
            $this->result->setError($message, Response::HTTP_BAD_REQUEST);
        }

        return $this->result;
    }

    /**
     * Prepare the response data for a successful login.
     */
    public function refreshToken(Request $request): ServiceResult
    {
        $user = $request->user();
        // Revoke the used refresh token
        $user->currentAccessToken()->delete();

        $tokens = $this->generateTokens($user);
        $result = $this->prepareRefreshTokenResponse($tokens);

        $this->result->setData($result, ResultType::JSON);

        return $this->result;
    }

    /**
     * User logout
     */
    public function userLogout(Request $request): ServiceResult
    {
        try {
            $user = $request->user();
            // Revoke all tokens
            $user->tokens()->delete();

            // Log the logout event
            $loginInfo = [
                'logout_at' => Carbon::now()->toDateTimeString(),
                'ip' => request()->getClientIp(),
                'device' => Browser::deviceType(),   // Fetching device type
                'browser' => Browser::browserName(),   // Fetching browser name
                'platform' => Browser::platformName(),  // Fetching platform name
            ];
            activity()
                ->performedOn($user)   // Log activity on the logged-out user model
                ->withProperties($loginInfo)  // Attach logout details to the activity log
                ->log('logout');

            $this->result->setData(['message' => 'Successfully logged out.'], ResultType::META);
        } catch (Exception $exception) {
            $message = 'User logout failed.';

            $this->keepLog('error', $message, $exception->getMessage());
            $this->result->setError($message, Response::HTTP_BAD_REQUEST);
        }

        return $this->result;
    }

    /**
     * Send Password Reset Link
     *
     *
     * @throws ValidationException
     */
    public function sendPasswordResetLink(ForgotPasswordRequest $request): ServiceResult
    {
        try {
            $status = Password::sendResetLink(
                $request->only('email')
            );

            if ($status !== Password::RESET_LINK_SENT) {
                throw ValidationException::withMessages([
                    'email' => [trans($status)],
                ]);
            }

            $this->result->setData(['message' => __($status)], ResultType::META);
        } catch (Exception $exception) {
            $message = 'Failed to send password reset email.';

            $this->keepLog('error', $message, $exception->getMessage());
            $this->result->setError($message, Response::HTTP_BAD_REQUEST);
        }

        return $this->result;
    }

    public function setPassword(NewPasswordRequest $request): ServiceResult
    {
        try {
            $status = Password::reset(
                $request->only('email', 'password', 'password_confirmation', 'token'),
                function ($user) use ($request) {
                    $user->forceFill([
                        'password' => Hash::make($request->string('password')),
                        'remember_token' => Str::random(60),
                        'status' => UserStatus::ACTIVE,
                        'email_verified_at' => now(),
                    ])->save();
                }
            );

            if ($status !== Password::PASSWORD_RESET) {
                throw ValidationException::withMessages([
                    'email' => [trans($status)],
                ]);
            }

            $this->result->setData(['message' => 'Your password has been set'], ResultType::META);
        } catch (Exception $exception) {
            $message = 'Failed to set password.';

            $this->keepLog('error', $message, $exception->getMessage());
            $this->result->setError($message, Response::HTTP_BAD_REQUEST);
        }

        return $this->result;
    }

    /**
     * Set new password
     *
     *
     * @throws ValidationException
     */
    public function setNewPassword(NewPasswordRequest $request): ServiceResult
    {
        try {
            $status = Password::reset(
                $request->only('email', 'password', 'password_confirmation', 'token'),
                function ($user) use ($request) {
                    $user->forceFill([
                        'password' => Hash::make($request->string('password')),
                        'remember_token' => Str::random(60),
                    ])->save();

                    event(new PasswordReset($user));
                }
            );

            if ($status !== Password::PASSWORD_RESET) {
                throw ValidationException::withMessages([
                    'email' => [trans($status)],
                ]);
            }

            $this->result->setData(['message' => __($status)], ResultType::META);
        } catch (Exception $exception) {
            $message = 'Failed to set new password.';

            $this->keepLog('error', $message, $exception->getMessage());
            $this->result->setError($message, Response::HTTP_BAD_REQUEST);
        }

        return $this->result;
    }

    /**
     * Send email verify link
     *
     *
     * @throws ValidationException
     */
    public function sendEmailVerifyLink(Request $request): ServiceResult
    {
        try {
            if ($request->user()->hasVerifiedEmail()) {
                $this->result->setData(['message' => 'Your email is already verified.'], ResultType::META);
            } else {
                $request->user()->sendEmailVerificationNotification();
                $this->result->setData(['message' => 'verification-link-sent'], ResultType::META);
            }
        } catch (Exception $exception) {
            $message = 'Failed to send email verification notification.';

            $this->keepLog('error', $message, $exception->getMessage());
            $this->result->setError($message, Response::HTTP_BAD_REQUEST);
        }

        return $this->result;
    }

    /**
     * Verify Email
     *
     *
     * @throws ValidationException
     */
    public function verifyEmail(EmailVerificationRequest $request): ServiceResult
    {
        try {
            if ($request->user()->hasVerifiedEmail()) {
                $this->result->setData(['message' => 'Your email is already verified.'], ResultType::META);

                return $this->result;
            }

            if ($request->user()->markEmailAsVerified()) {
                event(new Verified($request->user()));
            }

            $this->result->setData(['message' => 'Successfully verified.'], ResultType::META);
        } catch (Exception $exception) {
            $message = 'Failed to mark email as verified.';

            $this->keepLog('error', $message, $exception->getMessage());
            $this->result->setError($message, Response::HTTP_BAD_REQUEST);
        }

        return $this->result;
    }

    private function generateTokens($user): array
    {
        $accessTokenExpireTime = Carbon::now()->addMinutes(config('sanctum.access_token_expiration'));
        $refreshTokenExpireTime = Carbon::now()->addMinutes(config('sanctum.refresh_token_expiration'));

        $accessToken = $user->createToken('access_token', [TokenAbility::ACCESS_API], $accessTokenExpireTime);
        $refreshToken = $user->createToken('refresh_token', [TokenAbility::ISSUE_ACCESS_TOKEN], $refreshTokenExpireTime);

        return [
            'accessToken' => $accessToken->plainTextToken,
            'refreshToken' => $refreshToken->plainTextToken,
        ];
    }

    private function prepareLoginResponse($user, array $tokens): array
    {
        return [
            'jsonapi' => [
                'version' => '1.0',
            ],
            'meta' => [
                'success' => ['message' => 'Login successful.'],
                'user' => $user,
            ],
            'data' => [
                'type' => 'token',
                'attributes' => [
                    'access_token' => $tokens['accessToken'],
                    'refresh_token' => $tokens['refreshToken'],
                    'token_type' => 'Bearer',
                ],
            ],
        ];
    }

    private function prepareRefreshTokenResponse(array $tokens): array
    {
        return [
            'jsonapi' => [
                'version' => '1.0',
            ],
            'meta' => [
                'success' => ['message' => 'Access token successfully refreshed.'],
            ],
            'data' => [
                'type' => 'refresh-token',
                'attributes' => [
                    'access_token' => $tokens['accessToken'],
                    'refresh_token' => $tokens['refreshToken'],
                    'token_type' => 'Bearer',
                ],
            ],
        ];
    }
}
