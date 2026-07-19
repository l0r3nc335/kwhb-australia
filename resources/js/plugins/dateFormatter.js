// dateFormatter.js
export default {
  install(app) {
    app.config.globalProperties.$formatDate = function(dateString) {
      if(dateString != null)
      {
        const date = new Date(dateString);
        const formattedDate = date.toLocaleDateString('en-US', {
          year: 'numeric',
          month: '2-digit',
          day: '2-digit',
        });
        return formattedDate;
      }
      else
        return ""
    };
    app.config.globalProperties.$formatDateDMY= function (dateString) {
      if(dateString != null)
      {
        const date = new Date(dateString);
        const day = String(date.getDate()).padStart(2, '0');
        const month = String(date.getMonth() + 1).padStart(2, '0'); // Months are zero-based
        const year = date.getFullYear();
        
        return `${day}/${month}/${year}`;
      }
      else
        return ""
    };
    app.config.globalProperties.$capFirst= function (string) {
      if(string != null)
        return string.charAt(0).toUpperCase() + string.slice(1);
      else
        return "";
    };
  }
};
