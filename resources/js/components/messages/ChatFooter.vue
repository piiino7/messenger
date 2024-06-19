<template>
    <div class="chat-footer pb-3 pb-lg-10 position-static bottom-0 start-0">
        <!-- Chat: Files -->
        <div class="dz-preview bg-dark" id="dz-preview-row" data-horizontal-scroll="">
        </div>
        <h5 v-if="conversation.participants[0].isTyping" class="text-reset text-truncate mx-10 mb-4">is typing<span class='typing-dots'><span>.</span><span>.</span><span>.</span></span></h5>


        <!-- Chat: Files -->
<!--        <div class="dz-preview bg-dark dz-preview-moved pb-10 pt-3 px-2" id="dz-preview-row" data-horizontal-scroll="" v-if="$root.updateMessage_id">
            <div class="theme-file-preview position-relative mx-2 dz-processing dz-error dz-complete">
                <div class="message-text">
                    <p>Edit message</p>
                </div>
            </div>
            <a class="badge badge-circle bg-body text-white position-absolute top-0 end-0 m-3" href="#"
               data-dz-remove="">
                <svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2" fill="none"
                     stroke-linecap="round" stroke-linejoin="round" class="css-i6dzq1">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </a>
        </div>-->

        <div class="dz-preview bg-dark dz-preview-moved pb-10 pt-3 px-2" id="dz-preview-row" data-horizontal-scroll="" v-if="$root.updateMessage_id">
            <div class="theme-file-preview position-relative mx-7 dz-processing dz-error dz-complete">
                <h5 class="text-reset text-truncate">Edit message</h5>
            </div>
            <button type="button" @click="stopEditing()" class="align-bottom btn-close btn-close-white opacity-100 position-absolute top-0 end-0 m-2" data-bs-dismiss="modal" aria-label="Close"></button>
<!--            <blockquote class="blockquote overflow-hidden" v-if="$root.updateMessage_id">
                <div class="message-text row">
                    <div class="col-xl-11 d-xl-block text-primary">Edit message</div>
                    <div class="col-xl-1 ps-lg-12">
                        <button type="button" @click="stopEditing()" class="align-bottom btn-close btn-close-white opacity-100" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                </div>
            </blockquote>-->
        </div>

        <div class="dz-preview bg-dark dz-preview-moved pb-10 pt-3 px-2" id="dz-preview-row" data-horizontal-scroll="" v-if="$root.forwardMessage_id">
            <blockquote class="blockquote overflow-hidden mx-4 px-4">
                <div class="d-xl-block">
                    <h6 class="text-reset text-truncate">{{ $root.forwardUser_name }}</h6>
                    <p class="small text-truncate">{{ forwardMessage }}</p>
                </div>
<!--                <div class="col-xl-1 ps-lg-12">
                    <button type="button" @click="stopForwarding()" class="align-bottom btn-close btn-close-white opacity-100" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>-->
                <button type="button" @click="stopForwarding()" class="align-bottom btn-close btn-close-white opacity-100 position-absolute top-0 end-0 m-2" data-bs-dismiss="modal" aria-label="Close"></button>
            </blockquote>
        </div>

        <div class="dz-preview bg-dark dz-preview-moved pb-10 pt-3 px-2" id="dz-preview-row" data-horizontal-scroll="" v-if="this.attachment">
            <div class="theme-file-preview position-relative mx-2 dz-processing dz-error dz-complete">
                <div class="avatar avatar-lg dropzone-file-preview">
                    <span class="avatar-text rounded bg-secondary text-body file-title" title="0e0f5f618036d46336b51259c3ac8953-1.mp4">
                        <svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round" class="css-i6dzq1">
                            <path d="M13 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"></path>
                            <polyline points="13 2 13 9 20 9"></polyline>
                        </svg>
                    </span>
                </div>
                <div class="avatar avatar-lg dropzone-image-preview">
                    <img src="#" class="avatar-img rounded file-title" data-dz-thumbnail="" alt="" title="0e0f5f618036d46336b51259c3ac8953-1.mp4">
                </div>
                <a class="badge badge-circle bg-body text-white position-absolute top-0 end-0 m-2" href="#"
                   data-dz-remove="">
                    <svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round" class="css-i6dzq1">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </a>
            </div>
        </div>

        <!-- Chat: Form -->
        <form class="chat-form rounded-pill bg-dark" data-emoji-form="" method="post" action="api/messages/" v-if="$root.updateMessage_id == null" @submit.prevent="sendMessage()">
            <input type="hidden" name="__token" :value="$root.csrfToken">
            <input type="hidden" name="conversation_id" :value="conversation? conversation.id : 0">
            <div class="row align-items-center gx-0">
                <div class="col-auto">
                    <a href="#" @click.prevent="selectFile()" class="btn btn-icon btn-link text-body rounded-circle" id="dz-btn">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-paperclip"><path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"></path></svg>
                    </a>
                </div>

                <div class="col">
                    <div class="input-group">
                        <textarea name = "message" v-model="message" @focus="$root.markAsRead()" @keypress="startTyping()" class="form-control px-0" placeholder="Type your message..." rows="1" data-emoji-input="" data-autosize="true"></textarea>

                        <a href="#" class="input-group-text text-body pe-0" data-emoji-btn="">
                                                <span class="icon icon-lg">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-smile"><circle cx="12" cy="12" r="10"></circle><path d="M8 14s1.5 2 4 2 4-2 4-2"></path><line x1="9" y1="9" x2="9.01" y2="9"></line><line x1="15" y1="9" x2="15.01" y2="9"></line></svg>
                                                </span>
                        </a>
                    </div>
                </div>

                <div class="col-auto">
                    <button class="btn btn-icon btn-primary rounded-circle ms-5">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-send"><line x1="22" y1="2" x2="11" y2="13"></line><polygon points="22 2 15 22 11 13 2 9 22 2"></polygon></svg>
                    </button>
                </div>
            </div>
        </form>

        <form class="chat-form rounded-pill bg-dark" data-emoji-form="" method="post" :action="{path:'api/messages/'+$root.updateMessage_id}" v-if="$root.updateMessage_id" @submit.prevent="sendUpdated()">
            <input type="hidden" name="__token" :value="$root.csrfToken">
            <input type="hidden" name="conversation_id" :value="conversation? conversation.id : 0">
            <div class="row align-items-center gx-0">
                <div class="col-auto">
                    <a href="#" @click.prevent="selectFile()" class="btn btn-icon btn-link text-body rounded-circle" id="dz-btn">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-paperclip"><path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"></path></svg>
                    </a>
                </div>

                <div class="col">
                    <div class="input-group">
                        <textarea name = "message" ref="editText" v-model="message" class="form-control px-0" placeholder="Edit your message..." rows="1" data-emoji-input="" data-autosize="true"></textarea>
                        <a href="#" class="input-group-text text-body pe-0" data-emoji-btn="">
                                                <span class="icon icon-lg">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-smile"><circle cx="12" cy="12" r="10"></circle><path d="M8 14s1.5 2 4 2 4-2 4-2"></path><line x1="9" y1="9" x2="9.01" y2="9"></line><line x1="15" y1="9" x2="15.01" y2="9"></line></svg>
                                                </span>
                        </a>
                    </div>
                </div>

                <div class="col-auto">
                    <button class="btn btn-icon btn-primary rounded-circle ms-5">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-send"><line x1="22" y1="2" x2="11" y2="13"></line><polygon points="22 2 15 22 11 13 2 9 22 2"></polygon></svg>
                    </button>
                </div>
            </div>
        </form>
        <!-- Chat: Form -->
    </div>
</template>
<script>
export default {
    props: ['conversation', 'messageToUpdate'],
    data() {
        return {
            message: "",
            forwardMessage: "",
            attachment: '',
            start_typing: false,
            timeout: null,
        }
    },
    updated() {
          if (this.$root.messageToUpdate && this.conversation.id == this.$root.conversation.id) {
              this.message = this.$root.messageToUpdate;
              this.$root.messageToUpdate = false;
          }
          if (this.$root.messageToForward && this.conversation.id == this.$root.conversation.id) {
              //this.message = "";
              this.forwardMessage = this.$root.messageToForward;
              this.$root.messageToForward = false;
          }
    },
    methods: {
        startTyping() {
            if (!this.start_typing) {
                this.start_typing = true;
                this.$root.chatChannel.whisper('typing', {
                    id: this.$root.userId,
                    conversation_id: this.$root.conversation.id,
                });
            }
            if (this.timeout) {
                clearTimeout(this.timeout);
            }
            this.timeout = setTimeout(() => {
                this.start_typing = false;
                this.$root.chatChannel.whisper('stopped-typing', {
                    id: this.$root.userId,
                    conversation_id: this.$root.conversation.id,
                });
            }, 2000);

        },
        sendMessage() {
            let data = {
                conversation_id: this.$root.conversation.id,
                message: this.message,
                _token: this.$root.csrfToken,
            };

            let formData = new FormData();
            formData.append('conversation_id', this.$root.conversation.id);
            formData.append('message', this.message);
            if (this.$root.forwardMessage_id)
            {
                formData.append('forwardMessage', this.$root.forwardMessage_id);
            }
            formData.append('_token', this.$root.csrfToken);
            if (this.attachment) {
                formData.append('attachment', this.attachment);
            }

            fetch(`api/messages/`, {
                method: 'POST',
                headers: {
                    'Accept': "application/json",
                },
                body: formData,
            })
            .then(response => response.json())
            .then(json => {
                    console.log(JSON.stringify(json));
                    this.$root.messages.push(json);
                    if(json.type == 'attachment') {
                        this.$root.conversation.last_message.body = 'File';
                    } else {
                        this.$root.conversation.last_message.body = json.body;
                    }

                    let container = document.querySelector('#chat-body');
                    container.scrollTop = container.scrollHeight;
            });

            this.message = "";
            this.attachment = null;
            this.$root.forwardMessage_id = null;
        },
        sendUpdated() {
            fetch(`api/messages/${this.$root.updateMessage_id}/`, {
                method: 'PUT',
                mode: 'cors',
                headers: {
                    'Content-Type': "application/json",
                    'Accept': "application/json",
                },
                body: JSON.stringify({
                    user_id: this.$root.conversation.participants[0].id,
                    conversation_id: this.$root.conversation.id,
                    message: '"' +this.message + '"',
                    _token: this.$root.csrfToken,
                })
            })
                .then(response => response.json())
                .then(json => {
                    let index = this.$root.updateMessage_id;
                    let idx = this.$root.messages.findIndex(function(query) {
                        return query.id == index;
                    });
                    this.$root.messages[idx].body = this.message;
                    this.$root.updateMessage_id = null;
                    this.message = "";
                });
        },
        selectFile() {
            let fileElm = document.createElement('input');
            fileElm.setAttribute('type', 'file');
            fileElm.addEventListener('change', () => {
                if (fileElm.files.length == 0) {
                    return;
                }
                this.attachment = fileElm.files[0];
                //this.sendMessage();
            })
            fileElm.click();
        },
        stopEditing() {
            this.$root.updateMessage_id = null;
            this.message = "";
        },
        stopForwarding() {
            this.$root.forwardMessage_id = null;
            this.forwardMessage = null;
            this.$root.forwardUser_name = null;
        }
    }
}
</script>
