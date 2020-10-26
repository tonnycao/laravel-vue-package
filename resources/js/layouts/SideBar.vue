<template>
    <div class="sidebar-container" style="width: 200px">
        <ul class="sidebar" id="sidebar-main">
            <li><a href="/interco/task"><i class="fas fa-tasks"></i><span>InterCo Tasks</span></a></li>
            <li><a href="/users"><i class="fas fa-users"></i><span>Users List</span></a></li>
            <li><a href="/orc"><i class="fas fa-pen-square"></i><span>ORC List</span></a></li>
            <li><a href="/material"><i class="fas fa-certificate"></i><span>Materials</span></a></li>
            <li><a href="/customer"><i class="fas fa-user"></i><span>Customers</span></a></li>
        </ul>
    </div>
</template>

<script>
    export default {
        name: "SideBar"
    }
</script>