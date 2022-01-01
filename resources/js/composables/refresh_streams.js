import Swal from 'sweetalert2';
import userService from '../services/user';

export const refreshStreams = async (reload = false) => {
    try {
        await userService.refreshStreams();

        if (reload === true) {
            window.location.reload();
        }
    } catch (err) {
        Swal.fire({
            title: 'Error',
            icon: 'error',
            text: 'Could not refresh your streams data.'
        });
    }
}
