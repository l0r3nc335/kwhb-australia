export const ProfilePlaceHolder = (initials = 'MD') => {
  const canvas = document.createElement('canvas')
  const ctx = canvas.getContext('2d')

  // Set the canvas dimensions
  canvas.width = 100 // Adjust as needed
  canvas.height = 100 // Adjust as needed

  // Customize the font and other styles
  ctx.font = '45px Arial'
  ctx.fillStyle = '#fff' // Black color

  const text = initials

  // Calculate the text width and height
  const textWidth = ctx.measureText(text).width
  const textHeight = parseInt(ctx.font)

  // Calculate the center position
  const centerX = (canvas.width - textWidth) / 2
  const centerY = (canvas.height + textHeight) / 2

  // Draw the text on the canvas at the center
  ctx.fillText(text, centerX, centerY - 8)

  return canvas.toDataURL()
}

export const ProfilePlaceHolderSmall = (initials = 'MD') => {
  const canvas = document.createElement('canvas')
  const ctx = canvas.getContext('2d')

  // Set the canvas dimensions
  canvas.width = 50 // Adjust as needed
  canvas.height = 50 // Adjust as needed

  // Customize the font and other styles
  ctx.font = '20px Arial'
  ctx.fillStyle = '#fff' // Black color

  const text = initials

  // Calculate the text width and height
  const textWidth = ctx.measureText(text).width
  const textHeight = parseInt(ctx.font)

  // Calculate the center position
  const centerX = (canvas.width - textWidth) / 2
  const centerY = (canvas.height + textHeight) / 2

  // Draw the text on the canvas at the center
  ctx.fillText(text, centerX, centerY - 3)

  return canvas.toDataURL()
}
