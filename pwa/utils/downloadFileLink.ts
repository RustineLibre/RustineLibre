export const downloadFile = async (
  response: Response,
  filename: string
): Promise<void> => {
  const blob: Blob = await response.blob();
  const url = URL.createObjectURL(blob);
  const now = new Date();
  const formattedDate = now.toLocaleDateString('fr-FR').replace(/\//g, '-');
  const formattedTime = now
    .toLocaleTimeString('fr-FR', {hour: '2-digit', minute: '2-digit'})
    .replace(/:/g, '-');
  const fileName = `${filename}_${formattedDate}_${formattedTime}.csv`;
  const link = document.createElement('a');

  link.href = url;
  link.style.display = 'none';
  link.setAttribute('download', fileName);
  document.body.appendChild(link);
  link.click();

  if (link.parentNode) {
    link.parentNode.removeChild(link);
  }

  URL.revokeObjectURL(link.href);
};
