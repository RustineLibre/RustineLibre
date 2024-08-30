import {AbstractResource} from '@resources/AbstractResource';
import {User} from '@interfaces/User';
import {Collection, RequestHeaders, RequestParams} from '@interfaces/Resource';
import {getToken} from '@helpers/localHelper';
import {Repairer} from '@interfaces/Repairer';

class CustomerResource extends AbstractResource<User> {
  protected endpoint = '/customers';

  async exportCustomerCollectionCsv(
    repairerId: string | null
  ): Promise<Response> {
    const currentToken = getToken();

    return await fetch(`/export_customers_csv/${repairerId}`, {
      headers: {
        'Content-Type': 'text/csv',
        Authorization: `Bearer ${currentToken}`,
      },

      method: 'GET',
    });
  }

  async getAllByRepairer(
    repairer: Repairer,
    params?: RequestParams,
    headers?: RequestHeaders
  ): Promise<Collection<User>> {
    const doFetch = async () => {
      return await fetch(
        this.getUrl(`/repairers/${repairer.id}/customers`, params),
        {
          headers: {
            ...this.getDefaultHeaders(true),
            ...headers,
          },
        }
      );
    };

    return await this.getResult(doFetch, true);
  }
}

export const customerResource = new CustomerResource();
