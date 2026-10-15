import { useRef, useState } from 'react';
import axios from 'axios';

const api = axios.create({
    headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
});

// Keep the existing JSON endpoints; Axios sends Laravel's XSRF cookie automatically.
export default function useApiForm(initialValues = {}) {
    const [data, setData] = useState(initialValues);
    const [errors, setErrors] = useState({});
    const [message, setMessage] = useState('');
    const [processing, setProcessing] = useState(false);
    const pending = useRef(false);

    function setField(name, value) {
        setData((previous) => ({ ...previous, [name]: value }));
        setErrors((previous) => ({ ...previous, [name]: undefined }));
    }

    async function submit(method, url) {
        if (pending.current) return false;
        pending.current = true;
        setProcessing(true);
        setErrors({});
        setMessage('');
        try {
            await api.request({ method, url, data });
            return true;
        } catch (error) {
            setErrors(error.response?.data?.errors || {});
            setMessage(error.response?.status === 419
                ? 'Your session has expired. Refresh the page and try again.'
                : error.response?.data?.message || 'Unable to complete the request. Please try again.');
            return false;
        } finally {
            pending.current = false;
            setProcessing(false);
        }
    }

    return { data, setField, errors, message, processing, submit, setData };
}
