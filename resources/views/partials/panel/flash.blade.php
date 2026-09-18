@if (session('status'))
    <div class="alert alert-success alert-dismissible fade show material-shadow" role="alert">
        <strong>Success!</strong> {{ session('status') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if ($errors->any())
    <div class="alert alert-danger alert-dismissible fade show material-shadow" role="alert">
        <strong>Error!</strong> {{ $errors->first() }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif
