import { toast } from 'vue3-toastify';
export default (type, text) => {

  if (type === '') {
    toast(text)
   } else {
    toast[type](text)
  }
}
