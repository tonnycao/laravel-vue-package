export default {
    data() {
        return {
            formErrors: null
        }
    },
    methods: {
        handleFormException: function(err){
            if(err.response && err.response.data && err.response.data.errors){
                this.formErrors = err.response.data.errors;
            }else{
                this.showErr(err)
            }
        },
        getFormError: function (name) {
            if(this.formErrors && this.formErrors[name] ){
                return this.formErrors[name][0]
            }
            return null
        },
        clearErrors(){
            this.formErrors = null
        }
    }
};
