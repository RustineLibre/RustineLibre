import {
  Collection,
  RequestBody,
  RequestHeaders,
  RequestParams,
  ResponseError,
} from '@interfaces/Resource';

import {
  getRefreshToken,
  getToken,
  removeRefreshToken,
  removeToken,
  setRefreshToken,
  setToken,
} from '@helpers/localHelper';

import {ENTRYPOINT} from '@config/entrypoint';

export abstract class AbstractResource<T> {
  protected abstract endpoint: string;

  async get(
    id: string,
    withAuth: boolean = true,
    headers?: RequestHeaders
  ): Promise<T> {
    const doFetch = async () => {
      return await fetch(this.getUrl(id), {
        headers: {
          ...this.getDefaultHeaders(withAuth),
          ...headers,
        },
      });
    };

    return await this.getResult(doFetch, withAuth);
  }

  async getById(
    id: string,
    withAuth: boolean = true,
    headers?: RequestHeaders
  ): Promise<T> {
    const doFetch = async () => {
      return await fetch(this.getUrl() + '/' + id, {
        headers: {
          ...this.getDefaultHeaders(withAuth),
          ...headers,
        },
      });
    };

    return await this.getResult(doFetch, withAuth);
  }

  async getAll(
    withAuth: boolean = true,
    params?: RequestParams,
    headers?: RequestHeaders
  ): Promise<Collection<T>> {
    const doFetch = async () => {
      return await fetch(this.getUrl(undefined, params), {
        headers: {
          ...this.getDefaultHeaders(withAuth),
          ...headers,
        },
      });
    };

    return await this.getResult(doFetch, withAuth);
  }

  async post(body: RequestBody = {}, headers?: RequestHeaders): Promise<T> {
    const doFetch = async () => {
      return await fetch(this.getUrl(), {
        headers: {
          ...this.getDefaultHeaders(),
          ...headers,
        },
        method: 'POST',
        body: JSON.stringify(body),
      });
    };

    return await this.getResult(doFetch);
  }

  async put(
    id: string,
    body: RequestBody = {},
    headers?: RequestHeaders,
    withAuth: boolean = true
  ): Promise<T> {
    const doFetch = async () => {
      return await fetch(this.getUrl(id), {
        headers: {
          ...this.getDefaultHeaders(withAuth),
          ...headers,
        },
        method: 'PUT',
        body: JSON.stringify(body),
      });
    };

    return await this.getResult(doFetch);
  }

  async patch(
    id: string,
    body: RequestBody = {},
    headers?: RequestHeaders
  ): Promise<T> {
    const doFetch = async () => {
      const defaultHeaders: Record<string, string> = {
        Accept: 'application/ld+json',
        'Content-Type': 'application/merge-patch+json',
      };

      const currentToken = getToken();
      if (!!currentToken) {
        defaultHeaders['Authorization'] = `Bearer ${currentToken}`;
      }

      return await fetch(this.getUrl(id), {
        headers: {
          ...defaultHeaders,
          ...headers,
        },
        method: 'PATCH',
        body: JSON.stringify(body),
      });
    };

    return await this.getResult(doFetch);
  }
  async putById(
    id: string,
    body: RequestBody = {},
    headers?: RequestHeaders
  ): Promise<T> {
    const doFetch = async () => {
      return await fetch(`${this.getUrl()}/${id}`, {
        headers: {
          ...this.getDefaultHeaders(),
          ...headers,
        },
        method: 'PUT',
        body: JSON.stringify(body),
      });
    };

    return await this.getResult(doFetch);
  }

  async patchById(
    id: string,
    body: RequestBody = {},
    headers?: RequestHeaders
  ): Promise<T> {
    const doFetch = async () => {
      return await fetch(`${this.getUrl()}/${id}`, {
        headers: {
          ...this.getDefaultHeadersPatch(),
          ...headers,
        },
        method: 'PATCH',
        body: JSON.stringify(body),
      });
    };

    return await this.getResult(doFetch);
  }

  async delete(iri: string, headers?: RequestHeaders): Promise<void> {
    const response = await fetch(this.getUrl(iri), {
      headers: {
        ...this.getDefaultHeaders(),
        ...headers,
      },
      method: 'DELETE',
    });

    // handle response's error
    if (response && !response.ok) {
      const result = await response.json();
      throw new Error((result as ResponseError)['hydra:description']);
    }
  }

  async deleteById(id: string, headers?: RequestHeaders): Promise<void> {
    const response = await fetch(`${this.getUrl()}/${id}`, {
      headers: {
        ...this.getDefaultHeaders(),
        ...headers,
      },
      method: 'DELETE',
    });

    // handle response's error
    if (response && !response.ok) {
      const result = await response.json();
      throw new Error((result as ResponseError)['hydra:description']);
    }
  }

  protected getDefaultHeaders(
    withAuth: boolean = true
  ): Record<string, string> {
    const defaultHeaders: Record<string, string> = {
      Accept: 'application/ld+json',
      'Content-Type': 'application/ld+json',
    };

    if (withAuth) {
      const currentToken = getToken();
      if (!!currentToken) {
        defaultHeaders['Authorization'] = `Bearer ${currentToken}`;
      }
    }

    return defaultHeaders;
  }

  protected getDefaultFormDataHeaders(
    withAuth: boolean = true
  ): Record<string, string> {
    const defaultHeaders: Record<string, string> = {
      Accept: 'application/ld+json',
    };

    if (withAuth) {
      const currentToken = getToken();
      if (!!currentToken) {
        defaultHeaders['Authorization'] = `Bearer ${currentToken}`;
      }
    }

    return defaultHeaders;
  }

  protected getDefaultHeadersPatch(
    withAuth: boolean = true
  ): Record<string, string> {
    const defaultHeaders: Record<string, string> = {
      Accept: 'application/ld+json',
      'Content-Type': 'application/merge-patch+json',
    };

    if (withAuth) {
      const currentToken = getToken();
      if (!!currentToken) {
        defaultHeaders['Authorization'] = `Bearer ${currentToken}`;
      }
    }

    return defaultHeaders;
  }

  protected async handleResponse(response: Response, withAuth: boolean = true) {
    if (response.status === 204) {
      return '';
    }

    if (withAuth) {
      const refreshToken = await getRefreshToken();

      // in case of bad authentication we try to refresh the token if refresh_token exists
      if (response.status === 401 && !!refreshToken) {
        // ask API for new tokens
        const refreshResponse = await fetch(this.getUrl('/auth/refresh'), {
          headers: {
            'Content-Type': 'application/json',
          },
          method: 'POST',
          body: JSON.stringify({
            refresh_token: getRefreshToken(),
          }),
        });

        if (refreshResponse.ok) {
          // we store new tokens
          const {token, refresh_token} = await refreshResponse.json();
          setToken(token);
          setRefreshToken(refresh_token);
        } else {
          // this is bad user needs to authentication
          removeToken();
          removeRefreshToken();
        }
        throw 'unauthorized';
      }
    }

    const result = await response.json();

    // handle response's error
    if (!response.ok) {
      throw new Error((result as ResponseError)['hydra:description']);
    }

    return result;
  }

  public getUrl(id?: string, params?: RequestParams): string {
    const url = new URL(ENTRYPOINT + (undefined !== id ? id : this.endpoint));
    let queryMultipleMatch: string | null = null;

    if (undefined !== params) {
      Object.keys(params).forEach((key) => {
        const queryValue = params[key].toString();
        if (queryValue.includes('[]=')) {
          queryMultipleMatch = queryValue;
        } else {
          url.searchParams.append(key, queryValue);
        }
      });
    }

    let stringUrl: string = url.toString();
    if (queryMultipleMatch !== null) {
      stringUrl = `${stringUrl}&${queryMultipleMatch}`;
    }

    return stringUrl;
  }

  protected async getResult(
    fetchFunction: () => Promise<Response>,
    withAuth = true
  ) {
    let result;

    try {
      // first try
      const response = await fetchFunction();
      result = await this.handleResponse(response, withAuth);
    } catch (e) {
      if (e !== 'unauthorized') {
        throw e;
      }
      // something goes wrong about the first query, but token has been refresh, let's try again
      try {
        const response = await fetchFunction();
        result = await this.handleResponse(response, withAuth);
      } catch (e) {
        if (e === 'unauthorized') {
          // even after token refresh, the query fail, user should be logout by force
          removeToken();
          removeRefreshToken();
          window.location.href = '/';
        } else {
          throw e;
        }
      }
    }

    return result;
  }

  public getEndpoint(): string {
    return this.endpoint;
  }
}
