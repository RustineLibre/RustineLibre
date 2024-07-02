import {AbstractResource} from '@resources/AbstractResource';
import {User} from '@interfaces/User';
import {RequestHeaders} from '@interfaces/Resource';
import {getToken} from '@helpers/localHelper';

class CustomerResource extends AbstractResource<User> {
  protected endpoint = '/customers';

  async customersCsv(
    repairerId: string | null,
    headers?: RequestHeaders
  ): Promise<any> {
    const apiUrl = this.getUrl(`/export_customers_csv/${repairerId}`);

    const currentToken = getToken();
    const response = await fetch(apiUrl, {
      headers: {
        ...headers,
        'Content-Type': 'text/csv',
        Authorization: `Bearer ${currentToken}`,
      },

      method: 'GET',
    });

    const blob = await response.blob();
    const url = window.URL.createObjectURL(new Blob([blob]));
    const link = document.createElement('a');
    link.href = url;
    const now = new Date();
    const formattedDate = now.toLocaleDateString('fr-FR').replace(/\//g, '-');
    const formattedTime = now
      .toLocaleTimeString('fr-FR', {hour: '2-digit', minute: '2-digit'})
      .replace(/:/g, '-');
    const fileName = `export_clients_${formattedDate}_${formattedTime}.csv`;
    link.setAttribute('download', fileName);
    document.body.appendChild(link);
    link.click();
    if (link.parentNode) {
      link.parentNode.removeChild(link);
    }
  }
}

export const customerResource = new CustomerResource();
