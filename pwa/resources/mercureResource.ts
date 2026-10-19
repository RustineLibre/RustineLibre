import {AbstractResource} from '@resources/AbstractResource';

class MercureResource extends AbstractResource<void> {
  protected endpoint = '/.well-known/mercure';

  async subscribe(topics: string[]): Promise<EventSource> {
    // The API sets a cookie granting access to the private updates of the current user only
    await this.getResult(async () => {
      return await fetch(this.getUrl('/mercure_authorization'), {
        headers: this.getDefaultHeaders(),
        method: 'POST',
      });
    });

    const hub = new URL(this.getUrl());
    topics.forEach((topic) => hub.searchParams.append('topic', topic));

    return new EventSource(hub, {withCredentials: true});
  }
}

export const mercureResource = new MercureResource();
