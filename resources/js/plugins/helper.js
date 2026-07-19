



export const parseDate = (dateString) => {
  const parts = dateString.split('/');
  // Note: months are 0-based in JavaScript Date objects
  return new Date(parts[2], parts[1] - 1, parts[0]);
}
export const addOneDay = (dateString) => {
  const date = parseDate(dateString);
  date.setDate(date.getDate() + 1);

  
  const day = date.getDate();
  const month = date.getMonth() + 1; // Month is zero-based
  const year = date.getFullYear();

  // Format the date components as "d/m/y"
  return `${day}/${month}/${year}`;
  //return date.toLocaleDateString('en-US');
}

export const getDayName = (dateString) => {
  const days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
  const date = parseDate(dateString);
  const dayIndex = date.getDay();
  return days[dayIndex];
}

export const daysBetween = (startDate, endDate) => {
  const oneDay = 24 * 60 * 60 * 1000; // hours*minutes*seconds*milliseconds
  const start = parseDate(startDate);
  const end = parseDate(endDate);
  
  return Math.round(Math.abs((end - start) / oneDay));
}

