<?php

use App\Rules\HasEmailDomainMx;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

uses(TestCase::class);

it('accepts emails whose domain has mx records', function () {
    $validator = Validator::make(
        ['email' => 'user@gmail.com'],
        ['email' => ['required', 'email', new HasEmailDomainMx]],
    );

    expect($validator->passes())->toBeTrue();
});

it('rejects emails whose domain cannot receive mail', function () {
    $validator = Validator::make(
        ['email' => 'nobody@this-domain-definitely-does-not-exist-'.uniqid().'.invalid'],
        ['email' => ['required', 'email', new HasEmailDomainMx]],
    );

    expect($validator->fails())->toBeTrue();
});
