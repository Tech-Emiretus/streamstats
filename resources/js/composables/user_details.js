import Swal from 'sweetalert2';
import { ref } from 'vue';
import userService from '../services/user';

export const user = ref({});

export const getUserDetails = async () => {
    try {
        const response = await userService.getDetails();
        user.value = response.data;
    } catch (err) {
        Swal.fire({
            title: 'Error',
            icon: 'error',
            text: 'Could not fetch user details. Logging out.',
            allowOutsideClick: false
        }).then(() => {
            window.location.href = '/logout';
        });
    }
}
