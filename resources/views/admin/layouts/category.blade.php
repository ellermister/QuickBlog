<table class="table table-bordered">
    <thead>
    <tr>
        <th scope="col">#</th>
        <th scope="col">本站分类</th>
        <th scope="col">平台分类</th>
    </tr>
    </thead>
    <tbody>
    @foreach($category as $item)
        <tr>
            <th scope="row">{{$item->id}}</th>
            <td>{{$item->name ?? '默认分类'}}</td>
            <td>{{$item->platform_cat_name}}</td>
        </tr>
    @endforeach
    </tbody>
</table>