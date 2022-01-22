<!-- ============================================================== -->
<!-- left sidebar -->
<!-- ============================================================== -->
<div class="nav-left-sidebar sidebar-dark">
    <div class="menu-list">
        <nav class="navbar navbar-expand-lg navbar-light">
            <a class="d-xl-none d-lg-none" href="#">Dashboard</a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav flex-column">
                    <li class="nav-divider">
                        控制中心
                    </li>
                    @if($currentUser->isAdmin())
                    <li class="nav-item">
                        <a class="nav-link" href="#" data-toggle="collapse" aria-expanded="false" data-target="#submenu-1" aria-controls="submenu-1"><i class="fa fa-fw fa-rocket"></i>第三方平台</a>
                        <div id="submenu-1" class="collapse submenu" style="">
                            <ul class="nav flex-column">
                                <li class="nav-item">
                                    <a class="nav-link" href="/admin/platforms">平台管理</a>
                                </li>
                            </ul>
                        </div>
                    </li>
                    @endif
                    <li class="nav-item">
                        <a class="nav-link" href="#" data-toggle="collapse" aria-expanded="false" data-target="#submenu-user-manage" aria-controls="submenu-user-manage"><i class="fa fa-fw fa-rocket"></i>用户管理</a>
                        <div id="submenu-user-manage" class="collapse submenu" style="">
                            <ul class="nav flex-column">
                                <li class="nav-item">
                                    <a class="nav-link" href="/admin/user/new">新用户</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="/admin/user">用户列表</a>
                                </li>
                            </ul>
                        </div>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#" data-toggle="collapse" aria-expanded="false" data-target="#submenu-2" aria-controls="submenu-2"><i class="fa fa-fw fa-rocket"></i>博文管理</a>
                        <div id="submenu-2" class="collapse submenu" style="">
                            <ul class="nav flex-column">
                                <li class="nav-item">
                                    <a class="nav-link" href="/admin/post/new">新撰写 <span class="badge badge-secondary">New</span></a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="/admin/post">博文列表</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="/admin/category">分类</a>
                                </li>
                            </ul>
                        </div>
                    </li>
                    @if($currentUser->isAdmin())
                    <li class="nav-item">
                        <a class="nav-link" href="#" data-toggle="collapse" aria-expanded="false" data-target="#submenu-3" aria-controls="submenu-3"><i class="fa fa-fw fa-rocket"></i>系统管理</a>
                        <div id="submenu-3" class="collapse submenu" style="">
                            <ul class="nav flex-column">
                                <li class="nav-item">
                                    <a class="nav-link" href="/admin/setting">全局配置</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="/admin/log">同步日志</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="/admin/helper">登录助手</a>
                                </li>
                            </ul>
                        </div>
                    </li>
                    @endif
                </ul>
            </div>
        </nav>
    </div>
</div>
<!-- ============================================================== -->
<!-- end left sidebar -->