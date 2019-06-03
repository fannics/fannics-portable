
    <div id="alertMessages" @if (count($errors) <= 0)style="display: none"@endif class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
