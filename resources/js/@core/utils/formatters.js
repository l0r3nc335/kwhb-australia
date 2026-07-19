import moment from "moment";
import { isToday } from './index';

export const avatarText = value => {
  if (!value)
    return ''
  const nameArray = value.split(' ')
  
  return nameArray.map(word => word.charAt(0).toUpperCase()).join('')
}

// TODO: Try to implement this: https://twitter.com/fireship_dev/status/1565424801216311297
export const kFormatter = num => {
  const regex = /\B(?=(\d{3})+(?!\d))/g
  
  return Math.abs(num) > 9999 ? `${Math.sign(num) * +((Math.abs(num) / 1000).toFixed(1))}k` : Math.abs(num).toFixed(0).replace(regex, ',')
}

/**
 * Format and return date in Humanize format
 * Intl docs: https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Intl/DateTimeFormat/format
 * Intl Constructor: https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Intl/DateTimeFormat/DateTimeFormat
 * @param {String} value date to format
 * @param {Intl.DateTimeFormatOptions} formatting Intl object to format with
 */
export const formatDateIntl = (value, formatting = { month: 'short', day: 'numeric', year: 'numeric' }) => {
  if (!value)
    return value
  
  return new Intl.DateTimeFormat('en-US', formatting).format(new Date(value))
}

export const formatDate = (date, format) => {
    return moment(date).format(format)
}

/**
 * Return short human friendly month representation of date
 * Can also convert date to only time if date is of today (Better UX)
 * @param {String} value date to format
 * @param {Boolean} toTimeForCurrentDay Shall convert to time if day is today/current
 */
export const formatDateToMonthShort = (value, toTimeForCurrentDay = true) => {
  const date = new Date(value)
  let formatting = { month: 'short', day: 'numeric' }
  if (toTimeForCurrentDay && isToday(date))
    formatting = { hour: 'numeric', minute: 'numeric' }
  
  return new Intl.DateTimeFormat('en-US', formatting).format(new Date(value))
}
export const prefixWithPlus = value => value > 0 ? `+${value}` : value

export const convertToReadableTime = (minutes) => {
    if (minutes < 0) {
        return '0m'
    }
    let totalString = "";
    let hours = Math.floor(minutes / 60);
    minutes = minutes - (hours * 60)

    if (hours > 0) {
        totalString += `${hours}h`
    }

    totalString += ` ${minutes}m`

    return totalString
}

export const computeMinutesDifference = (start, end, breakTime) => {
    if (start && end) {
        let s = moment().format('YYYY-MM-DD') + ' ' + start;
        let e = moment().format('YYYY-MM-DD') + ' ' + end;

        let sd = moment(s);
        let se = moment(e);

        if (se.isBefore(sd)) {
            se.add(1, 'days');
        }

        return Math.abs(sd.diff(se, 'minutes')) - breakTime;
    }

    return 0;
}

export const getNextDayLabel = (entry) => {
    if (!([11,12,13].includes(entry.category?.id)) && entry.end < entry.start) {
        return moment(entry.date).add(1, 'days').format('ddd, DD MMMM')
    }

    return '';
}

export const timesheetLineTime = (timesheet) => {
  return computeMinutesDifference(timesheet.start, timesheet.end, timesheet.break)
}

export const timesheetsTotalTime = (timesheets) => {
  let total = 0

  for (let i in timesheets) {
      total += computeMinutesDifference(timesheets[i].start, timesheets[i].end, timesheets[i].break)
  }

  return total
}

