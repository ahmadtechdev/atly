<?php

namespace App\Enums;

enum VerificationCodeType: string
{
    case EmailVerification = 'email_verification';
    case PasswordReset = 'password_reset';
}
