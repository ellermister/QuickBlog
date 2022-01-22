@extends('admin.layouts.app')

@section('content')
    <div class="row">
        <!-- ============================================================== -->
        <!-- bordered table -->
        <!-- ============================================================== -->
        <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
            <div class="card">
                <h5 class="card-header">用户列表</h5>
                <div class="card-body">
                    <table class="table table-bordered">
                        <thead>
                        <tr>
                            <th scope="col">#</th>
                            <th scope="col">头像</th>
                            <th scope="col">名字</th>
                            <th scope="col">Email</th>
                            <th scope="col">最后登录时间</th>
                            @if($currentUser->isAdmin())
                            <th scope="col">IP</th>
                            @endif
                            <th scope="col">操作</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($users as $item)
                            <tr>

                                <th scope="row">{{$item->id}}</th>
                                <td>
                                    @if($item->avatar)
                                        <img src="{{$item->avatar}}" style="width: 48px;height:48px">
                                    @endif
                                </td>
                                <td>{{$item->name}}</td>
                                <td style="text-align: left;">{{$item->email}}</td>
                                <td style="text-align: left;">{{ date('Y-m-d H:i', $item->last_time) }}</td>
                                @if($currentUser->isAdmin())
                                    <td style="text-align: left;">{{ $item->last_ip }}</td>
                                @endif
                                @if($currentUser->isAdmin() || $item->id == $currentUser->id)
                                <td>
                                    <a href="/admin/user/{{$item->id}}" class="btn btn-success">编辑</a>
                                    <a href="javascript:void(0)" onclick="deletePost(this)" data-id="{{$item->id}}" class="btn btn-dark">删除</a>
                                </td>
                                @endif
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{$users->links()}}
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