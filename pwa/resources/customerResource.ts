import {AbstractResource} from '@resources/AbstractResource';
import {User} from '@interfaces/User';
import {getToken} from '@helpers/localHelper';

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
}

export const customerResource = new CustomerResource();
