// Origin factice servant uniquement à analyser une valeur relative : seul le résultat canonique est utilisé.
const BASE_ORIGIN = 'http://return-path.invalid';

// eslint-disable-next-line no-control-regex
const FORBIDDEN_CHARACTERS = /[\u0000-\u001f\u007f\\]/;

const isSafeRelativePath = (value: string): boolean =>
  value.startsWith('/') &&
  !value.startsWith('//') &&
  !FORBIDDEN_CHARACTERS.test(value);

/**
 * Valide la destination demandée après connexion ou inscription (paramètre `next`).
 *
 * Seul un chemin relatif à l'application est accepté (un seul "/" en tête, sans antislash ni
 * caractère de contrôle, y compris une fois décodé). Toute autre valeur (schéma `javascript:` ou
 * `data:`, URL absolue, protocole relatif, tableau de valeurs...) renvoie le chemin de repli.
 * Le chemin retourné est canonique : il est reconstruit à partir de l'URL analysée.
 */
export const getSafeReturnPath = (
  next: string | string[] | undefined,
  fallback = '/'
): string => {
  if (typeof next !== 'string' || !isSafeRelativePath(next)) {
    return fallback;
  }

  try {
    if (!isSafeRelativePath(decodeURIComponent(next))) {
      return fallback;
    }

    const url = new URL(next, BASE_ORIGIN);
    if (url.origin !== BASE_ORIGIN) {
      return fallback;
    }

    const canonical = `${url.pathname}${url.search}${url.hash}`;

    // La normalisation des segments de points peut produire un chemin protocole-relatif (/.//hote).
    return isSafeRelativePath(canonical) ? canonical : fallback;
  } catch {
    return fallback;
  }
};
