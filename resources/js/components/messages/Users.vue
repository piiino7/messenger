<template>
    <div class="card-list">
        <!-- Card -->
        <div class="card border-0" v-for="user in $root.users" v-bind:key="user.id">
            <input type="hidden" name="user_id" :value="user.id">
            <div class="card-body">

                <div class="row align-items-center gx-5">
                    <div class="col-auto">
                        <a href="#" class="avatar" :class = "{'avatar-online': user.isOnline}">
                            <img class="avatar-img" v-bind:src="user.avatar_url" alt="">
                        </a>
                    </div>

                    <div class="col">
                        <h5><a href="#">{{ user.name }}</a></h5>
                        <p v-if="user.isOnline" class="text-truncate">Online</p>
                        <p v-else class="text-truncate">{{ user.online }}</p>
                    </div>

                    <div class="col-auto">
                        <!-- Dropdown -->
                        <div class="dropdown">
                            <a class="icon text-muted" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-more-vertical"><circle cx="12" cy="12" r="1"></circle><circle cx="12" cy="5" r="1"></circle><circle cx="12" cy="19" r="1"></circle></svg>
                            </a>

                            <ul class="dropdown-menu">
                                <li>
                                    <form type="hidden" method="post" action="api/messenger/">
                                        <input type="hidden" name="__token" :value="$root.csrfToken">
                                        <button class="dropdown-item" @click.prevent="createConversation(user)">New message</button>
                                    </form>
                                </li>
                                <li><a class="dropdown-item" href="#">Edit contact</a>
                                </li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li>
                                    <a class="dropdown-item text-danger" href="#">Delete chat</a>
                                </li>
                            </ul>
                        </div>
                    </div>

                </div>

            </div>
        </div>
        <!-- Card -->

    </div>
</template>


<script>
    export default {
        data() {
            return {};
        },
        mounted() {
            fetch(`/api/messenger/`)
                .then(response => response.json())
                .then(json => {
                    console.log(JSON.stringify(json.data));
                    for (let i in json.data)
                    {
                        json.data[i].online = moment(json.data[i].online).format('MMMM Do YYYY, h:mm:ss a');
                    }
                    this.$root.users = json.data;
                });
        },
        methods: {
            createConversation(user) {
                this.$root.oneUser = user;
                fetch(`/api/messenger/`, {
                    method: 'POST',
                    mode: 'cors',
                    headers: {
                        "Content-Type": "application/json",
                    },
                    body: JSON.stringify({
                        _token: this.$root.csrfToken,
                        user_id: user.id,
                    })
                })
                    .then(response => response.json())
                    .then(json => {
                        for (let i in json.data)
                        {
                            json.data[i].participants[0].isOnline = false;
                        }
                        this.$root.conversations = json.conversationsArray.data;
                        this.$root.conversation = json.conversationsArray.data[json.conversationId.id - 1];
                    });
            },
        },
    }
</script>
