import Swal from 'sweetalert2';
import userService from '../services/user';

export const refreshStreams = async () => {
    try {
        await userService.refreshStreams();
    } catch (err) {
        Swal.fire({
            title: 'Error',
            icon: 'error',
            text: 'Could not refresh your streams data.'
        });
    }
}
