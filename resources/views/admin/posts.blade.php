@extends('admin.layouts.app')

@section('content')
    <div class="row">
        <!-- ============================================================== -->
        <!-- bordered table -->
        <!-- ============================================================== -->
        <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
            <div class="card">
                <h5 class="card-header">博文列表</h5>
                <div class="card-body">
                    <table class="table table-bordered">
                        <thead>
                        <tr>
                            <th scope="col">#</th>
                            <th scope="col">标题</th>
                            <th scope="col">可视</th>
                            <th scope="col">精选</th>
                            <th scope="col">操作</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($posts as $item)
                            <tr>

                                <th scope="row">{{$item->id}}</th>
                                <td>{{$item->title}}</td>
                                <td>@if($item->is_show == 1)<i class="fas fa-eye"></i>@else<i class="fas fa-eye-slash"></i>@endif</td>
                                <td>@if($item->featured == 0)<a href="/admin/post/{{$item->id}}/featured" class="btn btn-primary">设置精选</a>@else<a href="/admin/post/{{$item->id}}/featured" class="btn btn-dark">取消精选</a>@endif</td>
                                <td>
                                    <a href="/post/{{$item->id}}" target="_blank" class="btn btn-brand">预览</a>
                                    <a href="/admin/post/{{$item->id}}" class="btn btn-success">编辑</a>
                                    <a href="javascript:void(0)" onclick="deletePost(this)" data-id="{{$item->id}}" class="btn btn-dark">删除</a>
                                </td>

                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{$posts->links()}}
    </div>
@endsection

@section('scripts')
<script type="application/javascript">
    function deletePost(_this)
    {
        let postId = $(_this).data('id');
        console.log($(_this));
        if(confirm("确定要删除这篇博文吗？及相关联的平台文章不会删除，但文章关联性会删除。")){
            $.ajax({
                url: "/admin/post/" + postId,
                method:"delete",
                success:function(){
                    alert('删除成功');
                },error:function(){
                    alert('删除失败，请F12查看懒得写。');
                }
            });
        }
    }
</script>
@endsection