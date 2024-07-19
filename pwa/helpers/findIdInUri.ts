export default function findIdInUri(uri: string): string | null {
  return uri.split('/').pop() ?? null;
}
