// import the pages
import DataLoaderJobs from './../pages/DataLoaderJobs.vue'

// name the routes
export default [
    {
        path: '/dataloader/job',
        component: DataLoaderJobs,
        name: 'DataLoaderJobs',
        meta: {
            requiresAuth: true,
            showInMenu: true,
            roles: ['Admin', 'User'],
        }
    }
]
