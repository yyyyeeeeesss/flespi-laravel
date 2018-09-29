class FlespiService {
    constructor(url, token) {
        this.url = url
        this.token = token
    }

    subscrible(subscribles, callback) {
        let client = this.client()

        subscribles.forEach(v => {
            client.subscribe(v)
    });

        client.on('message',  callback)
    }

    client() {
        return mqtt.connect(this.url, {
            "username": this.token
        });
    }

    event(method, params) {

        let client = this.client()
        client.publish("v1/" + method, JSON.stringify({
            params
        }), {
            'properties': {
                'messageExpiryInterval': 3000
            }
        })

        //  client.end()
    }

    call(method, params, callback) {
        let topicResponse = "v1/"+method+"/reply/"+ Math.random().toString(36).substr(2, 9)
        let client = this.client()

        client.subscribe(topicResponse)
        client.on('message', (topic, message) => {
            if (topicResponse == topic) {
            callback(topic, message)
        }

        client.end()
    })

        client.publish("v1/" + method, JSON.stringify({
            params,
            topicResponse
        }))
    }
}

new Vue({
    el: '#app',
    data () {
        return {
            totalItems: 0,
            activator: 1,
            items: [],
            service: new FlespiService('wss://mqtt.flespi.io', "Hep59zVt8DMzf9IQS8XzlCoG6G5oBZu82H117Mi3fStiR1YyXJX8b9E5Gyk5lC3d"),
            loading: true,
            userRoles: [],
            pagination: {},
            pageItems: [5,10,25],
            search: null,
            modelRole: [],
            modelUserRole: [],
            editingRole: [],
            roles: [],
            colors: ['green', 'purple', 'indigo', 'cyan', 'teal', 'orange'],
            nonce: 0,
            editedItem: {
                title: '',
                roles: []
            },
            dialog: false,
            headers: [
                {
                    text: 'id',
                    align: 'left',
                    sortable: false,
                    value: 'id'
                },
                {
                    text: 'title',
                    align: 'right',
                    sortable: false,
                    value: 'title'
                },
                {
                    text: 'actions',
                    align: 'right',
                    sortable: false,
                    value: ''
                }
            ]
        }
    },
    watch: {
        modelRole(val, prev) {
            if (val.length === prev.length) return

            this.modelRole = val.map(v => {
                if (typeof v.title === 'string') {
                v = {
                    title: v.title,
                    id: v.id
                }

                this.roles.push(v)
                this.nonce++
            }

            return v
        })
        },

        pagination: {
            handler () {
                this.getDataFromApi({
                    'rowsPerPage': this.pagination.rowsPerPage,
                    'page': this.pagination.page,
                    'roles':  this.modelRole.map(function(currentValue, index, array) {
                        return currentValue.id;
                    })
                })
                    .then(data => {
                    this.items = data.items
                this.totalItems = data.total
            })
            },
            deep: true
        }
    },
    mounted () {
        this.subscrible()
        this.subscribleUpdateRole()

        this.getRolesFromApi()
            .then(data => {
            this.roles = data.items
        this.userRoles = data.items.map(function callback(currentValue, index, array) {
            return {"value": currentValue.id, "text": currentValue.title}
        })
    })

        this.getDataFromApi({
            'rowsPerPage': this.pagination.rowsPerPage,
            'page': this.pagination.page,
            'roles':  this.modelRole.map(function(currentValue, index, array) {
                return currentValue.id;
            })
        })
            .then(data => {
            this.items = data.items
        this.totalItems = data.total
    })
    },
    methods: {
        subscrible () {
            this.service.subscrible([
                'v1/events/update/user',
            ], (topic, message) => {
                this.getDataFromApi({
                'rowsPerPage': this.pagination.rowsPerPage,
                'page': this.pagination.page,
                'roles':  this.modelRole.map(function(currentValue, index, array) {
                    return currentValue.id;
                })
            })
                .then(data => {
                this.items = data.items
            this.totalItems = data.total
        })
        })
        },

        subscribleUpdateRole () {

            this.service.subscrible([
                'v1/events/update/role',
            ], (topic, message) => {
                let response = JSON.parse(message.toString())

                this.modelRole.push({
                id: response.id,
                title: response.title,
            })

            this.getDataFromApi({
                'rowsPerPage': this.pagination.rowsPerPage,
                'page': this.pagination.page,
                'roles':  this.modelRole.map(function(currentValue, index, array) {
                    return currentValue.id;
                })
            })
                .then(data => {
                this.items = data.items
            this.totalItems = data.total
        })
        })
        },

        getRolesFromApi() {
            return new Promise((resolve, reject) => {

                this.service.call('get/roles', [], (topic, message) => {
                let response = JSON.parse(message.toString())
                let items = response.items

                resolve({
                            items
                        })
            })
        })
        },

        getDataFromApi (options = {}) {
            this.loading = true
            return new Promise((resolve, reject) => {

                this.service.call('get/users', options, (topic, message) => {
                this.loading = false
            let response = JSON.parse(message.toString())
            let items = response.items
            let total = response.total

            resolve({
                items,
                total
            })
        })
        })
        },

        editItem (item) {
            this.editedIndex = this.items.indexOf(item)
            this.editedItem = Object.assign({}, item)

            this.editedItem.roles = this.editedItem.roles.map(function(currentValue, index, array) {
                return {"text": currentValue.title, "value": currentValue.id};
            });

            this.dialog = true
        },

        deleteItem (item) {
            const index = this.items.indexOf(item)
            confirm('Are you sure you want to delete this item?') && this.items.splice(index, 1)
        },

        close () {
            this.dialog = false
            this.modelUserRole = []
            setTimeout(() => {
                this.editedItem = Object.assign({}, this.defaultItem)
            this.editedIndex = -1
        }, 300)
        },

        save () {
            this.service.event('update/user', this.editedItem)
            this.modelUserRole = []
            this.close()
        },

        editRole (index, item) {
            if (!this.editingRole) {
                this.editingRole = item
                this.indexRole = index
            } else {
                this.service.event('update/role', this.editingRole)
                this.editingRole = null
                this.indexRole = -1
            }
        },

        changeRole()
        {
            this.getDataFromApi({
                'rowsPerPage': this.pagination.rowsPerPage,
                'page': this.pagination.page,
                'roles':  this.modelRole.map(function(currentValue, index, array) {
                    return currentValue.id;
                })
            })
                .then(data => {
                this.items = data.items
            this.totalItems = data.total
        })
        },

        createRole() {
            this.service.event('update/role', {'title': this.search})
        },

        filter (item, queryText, itemText) {

            const hasValue = val => val != null ? val : ''
            const text = hasValue(itemText)
            const query = hasValue(queryText)

            return text.title.toString()
                .toLowerCase()
                .indexOf(query.toString().toLowerCase()) > -1
        }
    }
})


