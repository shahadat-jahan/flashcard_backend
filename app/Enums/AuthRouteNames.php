<?php

namespace App\Enums;

enum AuthRouteNames: string
{
    case REGISTER = 'register';
    case SET_PASSWORD = 'set.password';
    case LOGIN = 'login';
    case REFRESH_TOKEN = 'refresh.token';
    case FORGOT_PASSWORD = 'password.email';
    case RESET_PASSWORD = 'reset.password';
    case VERIFY_EMAIL = 'verification.verify';
    case EMAIL_VERIFY_NOTIFICATION = 'verification.send';
    case LOGOUT = 'logout';

    // These types used to verify JSON:API request type
    public function getResourceType(): string
    {
        return match ($this) {
            self::REGISTER => 'register',
            self::SET_PASSWORD => 'set-password',
            self::LOGIN => 'token',
            self::REFRESH_TOKEN => 'refresh-token',
            self::FORGOT_PASSWORD, self::RESET_PASSWORD => 'reset-password',
            self::VERIFY_EMAIL, self::EMAIL_VERIFY_NOTIFICATION => 'verify-email',
            self::LOGOUT => 'logout',
        };
    }
}
