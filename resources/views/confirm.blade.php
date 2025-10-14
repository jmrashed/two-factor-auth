@extends('two-factor::layout')

@section('card-body')
    <form method="post">
        @csrf
        <p class="text-center">
            {{ trans('two-factor::messages.continue') }}
        </p>
        <div class="form-row justify-content-center py-3">
            @if($errors->hasAny())
                <div class="col-12 alert alert-danger pb-0">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @php
                $twoFactorMethod = session('two_factor_method', 'totp'); // Default to TOTP
            @endphp

            <div class="col-sm-8 col-8 mb-3">
                <label for="two_factor_method" class="sr-only">{{ trans('two-factor::messages.two_factor_method') }}</label>
                <select name="two_factor_method" id="two_factor_method" class="form-control form-control-lg mb-3" onchange="this.form.submit()">
                    <option value="totp" {{ $twoFactorMethod === 'totp' ? 'selected' : '' }}>{{ trans('two-factor::messages.totp_method') }}</option>
                    @if (config('two-factor.sms.enabled') && $user->phone_number_verified_at)
                        <option value="sms" {{ $twoFactorMethod === 'sms' ? 'selected' : '' }}>{{ trans('two-factor::messages.sms_method') }}</option>
                    @endif
                    @if (config('two-factor.email.enabled') && $user->email_2fa_enabled)
                        <option value="email" {{ $twoFactorMethod === 'email' ? 'selected' : '' }}>{{ trans('two-factor::messages.email_method') }}</option>
                    @endif
                </select>

                @if ($twoFactorMethod === 'sms')
                    <button type="submit" name="action" value="send_sms_code" class="btn btn-secondary btn-block mb-3">{{ trans('two-factor::messages.send_code') }}</button>
                @elseif ($twoFactorMethod === 'email')
                    <button type="submit" name="action" value="send_email_code" class="btn btn-secondary btn-block mb-3">{{ trans('two-factor::messages.send_code') }}</button>
                @endif

                <input type="text" name="2fa_code" id="2fa_code"
                       class="@error('2fa_code') is-invalid @enderror form-control form-control-lg"
                       minlength="6" placeholder="123456" required>
            </div>
            <div class="w-100"></div>
            <div class="col-auto mb-3">
                <button type="submit" class="btn btn-primary btn-lg">
                    {{ trans('two-factor::messages.confirm') }}
                </button>
            </div>
        </div>
    </form>
@endsection
