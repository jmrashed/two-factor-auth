@php($suffix = $suffix ?? '')
<div class="form-group mb-2">
    <label for="current_password{{ $suffix }}">{{ trans('two-factor::messages.current_password') }}</label>
    <input type="password" name="current_password" id="current_password{{ $suffix }}" class="form-control @error('current_password') is-invalid @enderror" required>
    @error('current_password')
        <span class="invalid-feedback" role="alert">
            <strong>{{ $message }}</strong>
        </span>
    @enderror
</div>
