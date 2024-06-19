<template>
        <div class="card-body" v-for="user in $root.mainUser" v-bind:key="user.id">
            <div class="row align-items-center gx-5">
                <div class="col-auto">
                    <div class="avatar">
                        <img class="avatar-img" v-bind:src="user.avatar_url" alt="">

                        <div class="badge badge-circle bg-secondary border-outline position-absolute bottom-0 end-0">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-image"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><circle cx="8.5" cy="8.5" r="1.5"></circle><polyline points="21 15 16 10 5 21"></polyline></svg>
                        </div>
                        <input id="upload-profile-photo" class="d-none" @click.prevent="changeAvatar()" type="file">
                        <label class="stretched-label mb-0" for="upload-profile-photo"></label>
                    </div>
                </div>
                <div class="col">
                    <h5>{{ user.name }}</h5>
                    <p>{{ user.email }}</p>
                </div>
                <div class="col-auto">
                    <a href="#" class="text-muted">
                        <div class="icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-log-out"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
                        </div>
                    </a>
                </div>
            </div>
        </div>
</template>

<script>
export default {
    data() {
        return {
            newProfileAvatar: '',
        };
    },
    mounted() {
        fetch(`/api/messenger/getMe`)
            .then(response => response.json())
            .then(json => {
                this.$root.mainUser = json.data;
            });
    },
    methods: {
        changeAvatar() {
            if (upload-profile-photo.files.length == 0) {
                return;
            }
            this.newProfileAvatar = upload-profile-photo.files[0];
        }
    },
}

</script>


