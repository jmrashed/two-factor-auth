@extends('two-factor::layout')

@section('card-body')
    <h3 class="card-title text-center mb-4">{{ trans('two-factor::messages.two_factor_authentication') }}</h3>

    @if (session('status'))
        <div class="alert alert-success" role="alert">
            {{ session('status') }}
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger" role="alert">
            {{ session('error') }}
        </div>
    @endif

    @if (session('recovery_codes'))
        <div class="alert alert-warning">
            <strong>{{ trans('two-factor::messages.recovery_code.generated') }}</strong>
            <ul class="mb-0">
                @foreach (session('recovery_codes') as $recoveryCode)
                    <li><code>{{ $recoveryCode }}</code></li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('two-factor.settings.update') }}">
        @csrf
        @method('PUT')

        {{-- TOTP Settings --}}
        <div class="form-group mb-4">
            <div class="d-flex justify-content-between align-items-center">
                <label for="totp_enabled" class="mb-0">{{ trans('two-factor::messages.totp_method') }}</label>
                <div class="custom-control custom-switch">
                    <input type="checkbox" class="custom-control-input" id="totp_enabled" name="totp_enabled" {{ $user->hasTwoFactorEnabled() && $user->getTwoFactorMethod() === 'totp' ? 'checked' : '' }}>
                    <label class="custom-control-label" for="totp_enabled"></label>
                </div>
            </div>
            @if ($user->hasTwoFactorEnabled() && $user->getTwoFactorMethod() === 'totp')
                <div class="mt-3 text-center">
                    <p>{{ trans('two-factor::messages.scan_qr_code') }}</p>
                    {!! $qrCodeSvg !!}
                    <p class="mt-2">{{ trans('two-factor::messages.secret_key') }}: <strong>{{ $user->twoFactorAuth->shared_secret }}</strong></p>
                    <div class="form-group">
                        <label for="totp_code">{{ trans('two-factor::messages.verify_code') }}</label>
                        <input type="text" name="totp_code" id="totp_code" class="form-control @error('totp_code') is-invalid @enderror" placeholder="123456">
                        @error('totp_code')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                    <button type="submit" name="action" value="confirm_totp" class="btn btn-primary mt-2">{{ trans('two-factor::messages.confirm_totp') }}</button>

                    @include('two-factor::partials.current-password', ['suffix' => '_totp'])
                    <button type="submit" name="action" value="disable_totp" class="btn btn-danger mt-2">{{ trans('two-factor::messages.disable_totp') }}</button>

                    <hr>
                    <p class="mt-2">{{ trans('two-factor::messages.recovery_codes_remaining') }}: {{ $user->getRecoveryCodes()->count() }}</p>
                    @include('two-factor::partials.current-password', ['suffix' => '_recovery'])
                    <button type="submit" name="action" value="regenerate_recovery_codes" class="btn btn-warning mt-2">{{ trans('two-factor::messages.regenerate_recovery_codes') }}</button>
                </div>
            @else
                <div class="mt-3 text-center">
                    <button type="submit" name="action" value="enable_totp" class="btn btn-primary">{{ trans('two-factor::messages.enable_totp') }}</button>
                </div>
            @endif
        </div>

        <hr>

        {{-- SMS Settings --}}
        <div class="form-group mb-4">
            <div class="d-flex justify-content-between align-items-center">
                <label for="sms_enabled" class="mb-0">{{ trans('two-factor::messages.sms_method') }}</label>
                <div class="custom-control custom-switch">
                    <input type="checkbox" class="custom-control-input" id="sms_enabled" name="sms_enabled" {{ $user->getTwoFactorMethod() === 'sms' ? 'checked' : '' }}>
                    <label class="custom-control-label" for="sms_enabled"></label>
                </div>
            </div>
            <div class="mt-3">
                <div class="form-group">
                    <label for="phone_number">{{ trans('two-factor::messages.phone_number') }}</label>
                    <input type="text" name="phone_number" id="phone_number" class="form-control @error('phone_number') is-invalid @enderror" value="{{ $user->twoFactorAuth->phone_number }}" placeholder="+1234567890">
                    @error('phone_number')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
                @if ($user->getTwoFactorMethod() === 'sms' && !$user->isPhoneNumberVerified())
                    <button type="submit" name="action" value="send_sms_code" class="btn btn-secondary mt-2">{{ trans('two-factor::messages.send_code') }}</button>
                    <div class="form-group mt-3">
                        <label for="sms_code">{{ trans('two-factor::messages.verify_sms_code') }}</label>
                        <input type="text" name="sms_code" id="sms_code" class="form-control @error('sms_code') is-invalid @enderror" placeholder="123456">
                        @error('sms_code')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                    <button type="submit" name="action" value="verify_sms" class="btn btn-primary mt-2">{{ trans('two-factor::messages.verify_sms') }}</button>
                @elseif ($user->getTwoFactorMethod() === 'sms' && $user->isPhoneNumberVerified())
                    <p class="text-success">{{ trans('two-factor::messages.phone_verified') }}</p>
                    @include('two-factor::partials.current-password', ['suffix' => '_sms'])
                    <button type="submit" name="action" value="disable_sms" class="btn btn-danger mt-2">{{ trans('two-factor::messages.disable_sms') }}</button>
                @else
                    <button type="submit" name="action" value="enable_sms" class="btn btn-primary mt-2">{{ trans('two-factor::messages.enable_sms') }}</button>
                @endif
            </div>
        </div>

        <hr>

        {{-- Email Settings --}}
        <div class="form-group mb-4">
            <div class="d-flex justify-content-between align-items-center">
                <label for="email_enabled" class="mb-0">{{ trans('two-factor::messages.email_method') }}</label>
                <div class="custom-control custom-switch">
                    <input type="checkbox" class="custom-control-input" id="email_enabled" name="email_enabled" {{ $user->isEmailTwoFactorEnabled() ? 'checked' : '' }}>
                    <label class="custom-control-label" for="email_enabled"></label>
                </div>
            </div>
            <div class="mt-3">
                @if ($user->isEmailTwoFactorEnabled())
                    <button type="submit" name="action" value="send_email_code" class="btn btn-secondary mt-2">{{ trans('two-factor::messages.send_code') }}</button>
                    <div class="form-group mt-3">
                        <label for="email_code">{{ trans('two-factor::messages.verify_email_code') }}</label>
                        <input type="text" name="email_code" id="email_code" class="form-control @error('email_code') is-invalid @enderror" placeholder="123456">
                        @error('email_code')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                    <button type="submit" name="action" value="verify_email" class="btn btn-primary mt-2">{{ trans('two-factor::messages.verify_email') }}</button>
                    @include('two-factor::partials.current-password', ['suffix' => '_email'])
                    <button type="submit" name="action" value="disable_email" class="btn btn-danger mt-2">{{ trans('two-factor::messages.disable_email') }}</button>
                @else
                    <button type="submit" name="action" value="enable_email" class="btn btn-primary mt-2">{{ trans('two-factor::messages.enable_email') }}</button>
                @endif
            </div>
        </div>

        <div class="form-group text-center mt-4">
            <button type="submit" class="btn btn-success btn-lg">{{ trans('two-factor::messages.save_settings') }}</button>
        </div>
    </form>
@endsection