@if (session('status'))
    <div class="alert alert-success mb_20"
        style="padding:15px;background-color:#dff0d8;color:#3c763d;border-radius:4px;">
        {{ session('status') }}
    </div>
@endif

@if ($errors->any())
    <div class="alert alert-danger mb_20"
        style="padding:15px;background-color:#f2dede;color:#a94442;border-radius:4px;">
        {{ $errors->first() }}
    </div>
@endif
