export default {
    methods: {
        getVal: function(str, obj, fallback='null'){
            let arr = str.split('.');
            for (let i = 0; i < arr.length; i++) {
                let key = arr[i];
                if((obj || {})[key]){
                    obj = obj[key];
                }else{
                    return fallback;
                }
            }
            return obj;
        },
        showErr: function(message){
            if(typeof message === 'object' && message !== null){
                message = this.getVal('response.data.message', message, '');
            }
            this.$bvModal.msgBoxOk((message || '') === '' ? 'An unexpected error occurred' : message, {
                title: this.$createElement('div', { domProps: { innerHTML: '<i class="fas fa-exclamation-circle text-danger"></i> Error' } }),
                size: 'sm',
                buttonSize: 'md',
                okVariant: 'light',
                footerClass: 'p-2',
                centered: true
            })
        },
        makeToast(messsage, variant = 'success') {
            this.$bvToast.toast(messsage, {
                title: this.$createElement('div', { domProps: { innerHTML: '<i class="fas fa-info-circle text-success"></i>' } }),
                variant: variant,
                solid: true
            })
        }
    }
};
