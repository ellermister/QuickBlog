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
                            <th scope="col">是否显示</th>
                            <th scope="col">操作</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($posts as $item)
                            <tr>

                                <th scope="row">{{$item->id}}</th>
                                <td>{{$item->title}}</td>
                                <td>{{$item->showText()}}</td>
                                <td>
                                    <a href="/admin/post/{{$item->id}}" class="btn btn-success">编辑</a>
                                    <a href="javascript:deletePost(this)" data-id="{{$item->id}}" class="btn btn-dark">删除</a>
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

<script type="application/javascript">
    function deletePost(_this)
    {
        let postId = $(_this).data('id');
        if(confirm("确定要删除这篇博文吗？及相关联的平台文章不会删除，但文章关联性会删除。")){
            $.ajax({
                url: "/admin/post/" + postId,
                method:"delete",
                dataType:'json',
                success:function(){
                    alert('删除成功');
                },error:function(){
                    alert('删除失败，请F12查看懒得写。');
                }
            });
        }
    }
</script>