<nav class="navbar navbar-expand-lg navbar-light navbar-fpt">
    <a href="/" class="navbar-brand" style=""><span class="logo"></span> {{config('app.name')}}</a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNavDropdown"
            aria-controls="navbarNavDropdown" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNavDropdown">
        @auth
        <ul class="navbar-nav" style="margin-left:20px;">
            @foreach(config('fpt.menu') as $main_menu_item)

                @if( count($main_menu_item['roles']) == 0 || array_intersect($main_menu_item['roles'], array_column(json_decode(Auth::user()->roles, true), 'role')) )

                    @if(count($main_menu_item['sub_menu_items']) < 1)

                        <li class="nav-item">
                            <a class="nav-link"
                               href="{{$main_menu_item['url']}}">{{$main_menu_item['main_menu_text']}}</a>
                        </li>

                    @else

                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button"
                               data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                {{$main_menu_item['main_menu_text']}}
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="navbarDropdown">

                                @foreach($main_menu_item['sub_menu_items'] as $sub_menu_item)

                                    @if( count($sub_menu_item['roles']) == 0 || array_intersect($sub_menu_item['roles'], array_column(json_decode(Auth::user()->roles, true), 'role')) )

                                        <li><a class="dropdown-item"
                                               href="{{$sub_menu_item['url']}}">{{$sub_menu_item['sub_menu_text']}}</a>
                                        </li>

                                    @endif

                                @endforeach

                            </ul>
                        </li>

                    @endif

                @endif

            @endforeach
        </ul>


        <ul class="navbar-nav ml-auto" style="margin-right:20px;">
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button"
                   data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="far fa-question-circle"></i> Help
                </a>
                <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                    <li><a class="dropdown-item" href="/access-groups">Access Groups</a></li>
                    <li><a class="dropdown-item" href="https://gbs-support.corp.apple.com/new-request/fdt-request/58">Support Request</a></li>
                </ul>
            </li>
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button"
                   data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="far fa-user"></i> {{ Auth::user()->name }}
                </a>
                <ul class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
                    <li><a class="dropdown-item" href="#" id="btn-user-details">Profile</a></li>
                    <li><a class="dropdown-item" href="/logout">Log Out</a></li>
                </ul>
            </li>
        </ul>
        @endauth

    </div>
</nav>

<div class="sidebar-container" style="width: 200px">
    <ul class="sidebar" id="sidebar-main">
        <li><a href="/interco/task"><i class="fas fa-tasks"></i><span>InterCo Tasks</span></a></li>
        <li><a href="/users"><i class="fas fa-users"></i><span>Users List</span></a></li>
        <li><a href="/orc"><i class="fas fa-pen-square"></i><span>ORC List</span></a></li>
        <li><a href="/material"><i class="fas fa-certificate"></i><span>Materials</span></a></li>
        <li><a href="/customer"><i class="fas fa-user"></i><span>Customers</span></a></li>
    </ul>
</div>