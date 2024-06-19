import { createApp } from "vue/dist/vue.esm-bundler";
import Messenger from "./components/messages/Messenger.vue";
import ChatList from "./components/messages/ChatList.vue";
import Users from "./components/messages/Users.vue";
import ProfilePicture from "./components/messages/ProfilePicture.vue";
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';
import users from "./components/messages/Users.vue";

window.Pusher = Pusher;



const chatApp = createApp({
    data() {
        return {
            conversations: [],
            conversation: null,
            messages: [],
            userId: userId,
            mainUser: null,
            csrfToken: csrf_token,
            laravelEcho: null,
            users: [],
            oneUser: null,
            chatChannel: null,
            deletedMessagesChannel: null,
            editedMessagesChannel: null,
            updateMessage_id: null,
            messageToUpdate: null,
            forwardMessage_id: null,
            messageToForward: null,
            forwardUser_name: null,
            newChat: null,
            onlineTime: null,
            alertAudio: new Audio('/assets/mixkit-correct-answer-tone-2870.wav'),
        }
    },
    mounted() {
        this.alertAudio.addEventListener('ended', () => {
            this.alertAudio.currentTime = 0; //current_time
        });

        this.laravelEcho = new Echo({
            broadcaster: 'pusher',
            key: import.meta.env.VITE_PUSHER_APP_KEY,
            cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER ?? 'mt1',
            wsHost: import.meta.env.VITE_PUSHER_HOST ? import.meta.env.VITE_PUSHER_HOST : `ws-${import.meta.env.VITE_PUSHER_APP_CLUSTER}.pusher.com`,
            wsPort: import.meta.env.VITE_PUSHER_PORT ?? 80,
            wssPort: import.meta.env.VITE_PUSHER_PORT ?? 443,
            forceTLS: (import.meta.env.VITE_PUSHER_SCHEME ?? 'https') === 'https',
            enabledTransports: ['ws', 'wss'],
        });
        this.laravelEcho.join(`Messenger.${this.userId}`)
                        .listen('.new-message', (data) => {
                            if (this.conversation && this.conversation.id == data.message.conversation_id) {
                                this.messages.push(data.message);
                                this.conversation.last_message = data.message;
                                this.conversation.new_messages++;
                                let container = document.querySelector('#chat-body');
                                container.scrollTop = container.scrollHeight;
                            } else {
                                let exists = false;
                                for (let i in this.conversations) {
                                    let conversation = this.conversations[i];
                                    if (conversation.id == data.message.conversation_id) {
                                        if (!conversation.hasOwnProperty('new_messages')) {
                                            conversation.new_messages = 0;
                                        }
                                        conversation.new_messages++;
                                        conversation.last_message = data.message;
                                        exists = true;
                                        break;
                                    }
                                }
                                if (!exists) {
                                    fetch(`/api/conversations/${data.message.conversation_id}`)
                                        .then(response => response.json())
                                        .then(json => {
                                            this.conversations.push(json);
                                        })
                                }
                            }
                            this.alertAudio.play();
                        });
        this.chatChannel = this.laravelEcho.join(`Chat`)
                        .here((users) => {
                            for (let i in users) {
                                for (let n in this.conversations) {
                                    if (users[i].id == this.conversations[n].participants[0].id) {
                                        this.conversations[n].participants[0].isOnline = true;
                                    }
                                }
                                for (let n in this.users) {
                                    if (users[i].id == this.users[n].id) {
                                        this.users[n].isOnline = true;
                                    }
                                }
                            }
                        })
                        .joining((user) => {
                            for (let i in this.conversations) {
                                let conversation = this.conversations[i];
                                if (conversation.participants[0].id == user.id) {
                                    this.conversations[i].participants[0].isOnline = true;
                                }
                            }
                            for (let n in this.users) {
                                if (user.id == this.users[n].id) {
                                    this.users[n].isOnline = true;
                                }
                            }
                        })
                        .leaving((user) => {
                            for (let n in this.users) {
                                if (user.id == this.users[n].id) {
                                    this.users[n].isOnline = false;
                                    this.markLastOnline(user.id);
                                    this.users[n].online = moment().format('MMMM Do YYYY, h:mm:ss a');
                                }
                            }

                            for (let i in this.conversations) {
                                let conversation = this.conversations[i];
                                if (conversation.participants[0].id == user.id) {
                                    this.conversations[i].participants[0].isOnline = false;
                                    this.markLastOnline(user.id);
                                    this.$root.onlineTime = moment().fromNow();
                                    //this.$root.onlineTime = moment().format('MMMM Do YYYY, h:mm:ss a');
                                    this.$root.conversation.participants[0].online  = moment();
                                }
                            }
                        })
                        .listenForWhisper('typing', (e) => {
                            let user = this.findUser(e.id, e.conversation_id);
                            if (user) {
                                user.isTyping = true;
                            }
                        })
                        .listenForWhisper('stopped-typing', (e) => {
                            let user = this.findUser(e.id, e.conversation_id);
                            if (user) {
                                user.isTyping = false;
                            }
                        });
        this.deletedMessagesChannel = this.laravelEcho.join(`DeletedMessages.${this.userId}`)
                        .listen('.deleted-message', (data) => {
                            if (this.conversation && this.conversation.id == data.message.conversation_id) {
                                let idx = this.messages.findIndex(function(query) {
                                    return query.id == data.message.id;
                                });
                                this.messages.splice(idx, 1);
                                let container = document.querySelector('#chat-body');
                                container.scrollTop = container.scrollHeight;
                            }
                        });
        this.editedMessagesChannel = this.laravelEcho.join(`EditedMessages.${this.userId}`)
                        .listen('.edited-message', (data) => {
                            if (this.conversation && this.conversation.id == data.message.conversation_id) {
                                let idx = this.messages.findIndex(function(query) {
                                    return query.id == data.message.id;
                                });
                                this.messages[idx].body = data.message.body;
                                let container = document.querySelector('#chat-body');
                                container.scrollTop = container.scrollHeight;
                            }
                        });
    },
    methods: {
        moment(time) {
            return moment(time);
        },
        isOnline(user) {
            for (let i in this.users) {
                if (this.users[i].id == user.id)
                    return this.users[i].isOnline;
            }
            return false;
        },
        findUser(id, conversation_id) {
            for (let i in this.conversations) {
                let conversation = this.conversations[i];
                if (conversation_id === conversation.id && conversation.participants[0].id == id) {
                    return this.conversations[i].participants[0];
                }
            }
        },
        markAsRead(conversation = null) {
            if (conversation == null) {
                conversation = this.conversation;
            }
            fetch(`api/conversations/${conversation.id}/read`, {
                method: 'PUT',
                mode: 'cors',
                headers: {
                    'Content-Type': "application/json",
                    'Accept': "application/json",
                },
                body: JSON.stringify({
                    _token: this.csrfToken,
                })
            })
                .then(response => response.json())
                .then(json => {
                    conversation.new_messages = 0;
                })
        },
        markLastOnline(user_id) {
            fetch(`api/conversations/${user_id}/online`, {
                method: 'PUT',
                mode: 'cors',
                headers: {
                    'Content-Type': "application/json",
                    'Accept': "application/json",
                },
                body: JSON.stringify({
                    _token: this.csrfToken,
                })
            })
        },
        deleteMessage(message, target) {
            fetch(`api/messages/${message.id}`, {
                method: 'DELETE',
                mode: 'cors',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    user_id: this.conversation.participants[0].id,
                    conversation_id: this.conversation.id,
                    message: message,
                    target: target,
                    _token: this.csrfToken,
                })
            })
                .then(response => response.json())
                .then(json => {
                    let idx = this.messages.indexOf(message);
                    this.messages.splice(idx, 1);
                    message.body = "Message deleted.";
                    if (message.id == this.$root.conversation.last_message.id) {
                        this.$root.conversation.last_message.body = "Message deleted.";
                    }
                })
        },
        updateMessage(message) {
            this.updateMessage_id = message.id;
            this.messageToUpdate = message.body;
            this.forwardMessage_id = null;
            this.messageToForward = null;
            this.forwardUser_name = null;
            //alert(this.messageToUpdate);
        },
        forwardMessage(message) {
            this.forwardMessage_id = message.id;
            this.messageToForward = message.body;
            this.forwardUser_name = message.user.name;
            this.updateMessage_id = null;
            this.messageToUpdate = null;
        }
    }
})
    .component('Messenger', Messenger)
    .component('ChatList', ChatList)
    .component('Users', Users)
    .component('ProfilePicture', ProfilePicture)
    .mount('#chat-app')
