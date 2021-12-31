import Swal from 'sweetalert2';
import userService from '../services/user';

export const refreshStreams = async (logoutOnError = false) => {
    try {
        await userService.refreshStreams();
    } catch (err) {
        Swal.fire({
            title: 'Error',
            icon: 'error',
            text: 'Could not refresh your streams data.'
        }).then(() => {
            if (logoutOnError === true) {
                window.location.href = '/logout';
            }
        });
    }
}
