import {AbstractResource} from '@resources/AbstractResource';
import {Repairer} from '@interfaces/Repairer';
import {RequestBody, RequestHeaders} from '@interfaces/Resource';
import {getToken} from '@helpers/localHelper';

class RepairerResource extends AbstractResource<Repairer> {
  protected endpoint = '/repairers';

  async postRepairerAndUser(
    body: RequestBody = {},
    headers?: RequestHeaders
  ): Promise<Repairer> {
    const url = this.getUrl('/create_user_and_repairer');

    const doFetch = async () => {
      return await fetch(url, {
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

  async exportRepairerCollectionCsv(): Promise<Response> {
    const currentToken = getToken();

    return await fetch('/export_repairers_csv', {
      headers: {
        'Content-Type': 'text/csv',
        Authorization: `Bearer ${currentToken}`,
      },
      method: 'GET',
    });
  }
}

export const repairerResource = new RepairerResource();
