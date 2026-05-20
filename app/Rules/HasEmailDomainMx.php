<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

/**
 * Verifies that the email's domain has at least one MX (mail exchanger)
 * record, falling back to an A record if MX lookups are unavailable.
 *
 * Note: this does NOT prove the specific mailbox exists. Mail-server-side
 * SMTP verification is unreliable and most providers (including Gmail)
 * deliberately refuse to confirm address existence to deter spam. We can
 * however rule out typos like "@gmial.com" and fake/empty domains.
 */
class HasEmailDomainMx implements ValidationRule
{
    /**
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || $value === '') {
            return;
        }

        $atIndex = strrpos($value, '@');

        if ($atIndex === false) {
            return;
        }

        $domain = strtolower(substr($value, $atIndex + 1));

        if ($domain === '') {
            $fail(__('This email address does not look valid.'));

            return;
        }

        if (! function_exists('checkdnsrr')) {
            return;
        }

        if (@checkdnsrr($domain, 'MX')) {
            return;
        }

        if (@checkdnsrr($domain, 'A')) {
            return;
        }

        $fail(__('The :domain domain does not exist or cannot receive email.', ['domain' => $domain]));
    }
}
