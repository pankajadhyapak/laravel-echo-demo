<template>
    <div class="container">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <p>All Users</p>
                <p v-for="user in onlineUsers">
                        {{ user.name }} is online
                </p>
                <p v-for="ct in currentlyTyping">
                        {{ ct }} is typing...
                </p>

                <div class="panel panel-default">
                    <div class="panel-heading">My Tasks</div>

                    <div class="panel-body">
                        <div class="row">
                            <div class="col-md-8">
                                <input @focus="typing()" @blur="notTyping()" type="text" class="form-control" v-model="newTask">
                            </div>
                            <div class="col-md-4">
                                <button class="btn btn-primary" @click="addTask()">Add Task</button>
                            </div>
                        </div>
                        <br>
                        <ul class="list-group">
                            <li v-for="task in tasks" class="list-group-item">
                                {{ task.name}} --> {{ task.done }}
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        mounted() {
            let vm = this;
            console.log('Component mounted.');

            Echo.join('chat.1')
                .here((users) => {
                    this.allUsers = users;
                })
                .joining((user) => {
                    this.allUsers.push(user);
                })
                .leaving((user) => {
                    this.allUsers = _.reject(this.allUsers, function(u) {
                        return u.id == user.id
                    });
                });

            Echo.private('newtask.1')
                .listen('NewTask', (e) => {
                    console.log(e);
                    this.tasks.push(e.task);
                });

            Echo.private('newtask.1')
                .listenForWhisper('typing', (e) => {
                    this.currentlyTypingUser.push(e.name);
                });

            Echo.private('newtask.1')
                .listenForWhisper('typing-stopped', (e) => {
                    console.log(e.name +" stopped typing");
                    var index = this.currentlyTypingUser.indexOf(e.name);
                    if (index > -1) {
                        this.currentlyTypingUser.splice(index, 1);
                    }
                    console.log(this.currentlyTypingUser);
                });

            axios.get("/gettask")
                .then(function(response){
                    vm.tasks = (response.data);
                });
        },
        data(){
            return {
                tasks:[],
                newTask:'',
                currentlyTypingUser:[],
                allUsers:[]
            }
        },
        computed:{
            currentlyTyping(){
                return _.sortedUniq(this.currentlyTypingUser);
            },
            onlineUsers(){
                _.remove((this.allUsers), function(u) {
                        return u.id == window.authUser.id
                    });
                return this.allUsers;
            }
        },
        methods:{
            typing(){
                Echo.private('newtask.1')
                    .whisper('typing', {
                        name: window.authUser.name
                    });
            },
            notTyping(){
                Echo.private('newtask.1')
                    .whisper('typing-stopped', {
                        name: window.authUser.name
                    });
            },
            addTask(){
                let vm = this;
                if(!vm.newTask){
                    alert("Task can't be empty");
                    return;
                }
                axios.post("/addtask", {"name": this.newTask}).then(function(response){
                    vm.tasks.push(response.data);
                    vm.newTask = '';
                });
            }
        }
    }
</script>
