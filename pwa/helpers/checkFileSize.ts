export const checkFileSize = (file: File, size = 5): boolean => {
  const maxSize = size * 1024 * 1024; // (default : 5 Mo)
  return file.size <= maxSize;
};
