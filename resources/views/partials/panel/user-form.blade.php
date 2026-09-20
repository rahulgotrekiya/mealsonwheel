@php($user = $user ?? null)

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label" for="firstname">First name</label>
                        <input type="text" class="form-control @error('firstname') is-invalid @enderror"
                            id="firstname" name="firstname" value="{{ old('firstname', $user?->firstname) }}" required>
                        @error('firstname')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label" for="lastname">Last name</label>
                        <input type="text" class="form-control @error('lastname') is-invalid @enderror" id="lastname"
                            name="lastname" value="{{ old('lastname', $user?->lastname) }}" required>
                        @error('lastname')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label" for="email">Email</label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror" id="email"
                            name="email" value="{{ old('email', $user?->email) }}" required>
                        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label" for="phone">Phone</label>
                        <input type="text" class="form-control @error('phone') is-invalid @enderror" id="phone"
                            name="phone" value="{{ old('phone', $user?->phone) }}">
                        @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                @php($editingSelf = $user && auth()->user()->is($user))

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label" for="role">Role</label>
                        <select class="form-select @error('role') is-invalid @enderror" id="role" name="role"
                            required @disabled($editingSelf)>
                            @foreach ($roles as $option)
                                <option value="{{ $option->value }}" @selected(old('role', $user?->role?->value) === $option->value)>
                                    {{ $option->label() }}
                                </option>
                            @endforeach
                        </select>
                        @error('role')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label" for="user-status">Status</label>
                        <select class="form-select @error('status') is-invalid @enderror" id="user-status" name="status"
                            required @disabled($editingSelf)>
                            @foreach ($statuses as $option)
                                <option value="{{ $option->value }}" @selected(old('status', $user?->status?->value) === $option->value)>
                                    {{ $option->label() }}
                                </option>
                            @endforeach
                        </select>
                        @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                @if ($editingSelf)
                    {{-- Submitted so validation passes; the controller ignores both for your own account. --}}
                    <input type="hidden" name="role" value="{{ $user->role->value }}">
                    <input type="hidden" name="status" value="{{ $user->status->value }}">
                    <p class="text-muted small">You cannot change your own role or status.</p>
                @endif

                <div class="mb-3">
                    <label class="form-label" for="password">Password</label>
                    {{-- Never prefilled. An empty field on an edit keeps the existing password. --}}
                    <input type="password" class="form-control @error('password') is-invalid @enderror" id="password"
                        name="password" autocomplete="new-password" @required(! $user)>
                    @if ($user)
                        <small class="text-muted">Leave empty to keep the current password.</small>
                    @endif
                    @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>

        <div class="text-end mb-3">
            <a href="{{ route('admin.users.index') }}" class="btn btn-light">Cancel</a>
            <button type="submit" class="btn btn-success w-sm">{{ $submitLabel }}</button>
        </div>
    </div>
</div>
