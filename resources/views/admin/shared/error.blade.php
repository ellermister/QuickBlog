@if ($errors->any())
    <div class="section-block">
        <div class="alert alert-danger">
            <ul  style="margin-bottom: 0;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>

        </div>
    </div>
@endif
@if(session('message'))
    <div class="section-block">
        <div class="alert alert-success" role="alert">
            {{ session('message')??'' }}<a href="#" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">×</span>
            </a>
        </div>
    </div>
@endif